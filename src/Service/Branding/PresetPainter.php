<?php
declare(strict_types=1);

namespace App\Service\Branding;

use App\Service\BrandingService;
use GdImage;
use InvalidArgumentException;

/**
 * Draws the ten shipped logos.
 *
 * The artwork is generated rather than checked in as opaque binaries so that it
 * can be re-cut at any size, recoloured, or extended with an eleventh emblem by
 * editing code that says what it is drawing. Every emblem is a short list of
 * points in a normalised frame: the origin sits at the centre of the medallion
 * and 1.0 is its inner radius, so a shape reads the same whether it is being
 * rendered for a 512px logo or a 16px favicon.
 *
 * Everything is drawn four times oversized and then scaled down. GD has no
 * antialiased polygon fill, and supersampling is what turns the stepped edge of
 * a sword blade into a clean one.
 */
final class PresetPainter
{
    /**
     * How much larger than the output the working canvas is.
     */
    private const SUPERSAMPLE = 4;

    /**
     * Metal tones, lightest first. Emblems are drawn from these so that the ten
     * presets look like one set even though each has its own field colour.
     *
     * @var array<string, array<string, array{int, int, int}>>
     */
    private const METALS = [
        'steel' => [
            'light' => [0xEE, 0xF3, 0xFA],
            'mid' => [0xB3, 0xC0, 0xD2],
            'dark' => [0x6F, 0x7D, 0x92],
            'edge' => [0x37, 0x41, 0x55],
        ],
        'gold' => [
            'light' => [0xFF, 0xE9, 0xA8],
            'mid' => [0xE8, 0xBF, 0x55],
            'dark' => [0xA9, 0x76, 0x1F],
            'edge' => [0x5B, 0x3D, 0x0C],
        ],
        'bone' => [
            'light' => [0xF9, 0xF4, 0xE4],
            'mid' => [0xDE, 0xD3, 0xB2],
            'dark' => [0xA5, 0x97, 0x72],
            'edge' => [0x55, 0x4B, 0x36],
        ],
        'stone' => [
            'light' => [0xE2, 0xDC, 0xD0],
            'mid' => [0xB6, 0xAE, 0x9C],
            'dark' => [0x7D, 0x75, 0x66],
            'edge' => [0x44, 0x3F, 0x38],
        ],
    ];

    /**
     * Field colour and metal for each preset.
     *
     * @var array<string, array{top: array{int, int, int}, bottom: array{int, int, int}, metal: string}>
     */
    private const PALETTES = [
        'dragon-crest' => [
            'top' => [0xB4, 0x23, 0x2F],
            'bottom' => [0x59, 0x0C, 0x16],
            'metal' => 'bone',
        ],
        'crossed-swords' => [
            'top' => [0x44, 0x5D, 0x80],
            'bottom' => [0x1B, 0x27, 0x3A],
            'metal' => 'steel',
        ],
        'knight-helm' => [
            'top' => [0x46, 0x4E, 0x5F],
            'bottom' => [0x14, 0x17, 0x1E],
            'metal' => 'steel',
        ],
        'castle-keep' => [
            'top' => [0x2D, 0x4C, 0x8A],
            'bottom' => [0x12, 0x1E, 0x42],
            'metal' => 'stone',
        ],
        'war-axe' => [
            'top' => [0x1E, 0x5C, 0x42],
            'bottom' => [0x0B, 0x28, 0x1E],
            'metal' => 'steel',
        ],
        'dragon-claw' => [
            'top' => [0x60, 0x30, 0x96],
            'bottom' => [0x27, 0x11, 0x44],
            'metal' => 'bone',
        ],
        'ogre-skull' => [
            'top' => [0x3C, 0x71, 0x22],
            'bottom' => [0x14, 0x2C, 0x0F],
            'metal' => 'bone',
        ],
        'crown-of-war' => [
            'top' => [0x4C, 0x1E, 0x55],
            'bottom' => [0x1C, 0x0A, 0x25],
            'metal' => 'gold',
        ],
        'tower-shield' => [
            'top' => [0x17, 0x60, 0x6F],
            'bottom' => [0x07, 0x27, 0x31],
            'metal' => 'gold',
        ],
        'war-banner' => [
            'top' => [0x9B, 0x43, 0x18],
            'bottom' => [0x45, 0x18, 0x08],
            'metal' => 'gold',
        ],
    ];

    /**
     * The rim around every medallion, darkest at the bottom.
     *
     * @var array{top: array{int, int, int}, bottom: array{int, int, int}}
     */
    private const RIM = [
        'top' => [0x3E, 0x46, 0x57],
        'bottom' => [0x15, 0x19, 0x21],
    ];

    /**
     * The canvas being drawn on.
     */
    private GdImage $im;

    /**
     * Canvas side, in supersampled pixels.
     */
    private int $size;

    /**
     * Centre of the medallion, in supersampled pixels.
     */
    private float $cx;

    private float $cy;

    /**
     * One unit of the normalised frame, in supersampled pixels.
     */
    private float $unit;

    /**
     * Metal tones of the preset being drawn.
     *
     * @var array<string, array{int, int, int}>
     */
    private array $metal;

    /**
     * Every slug this painter knows how to draw.
     *
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_keys(self::PALETTES);
    }

    /**
     * Render one preset.
     *
     * @param string $slug Which emblem to draw.
     * @param int $side Output size in pixels, square.
     * @param float $emblemScale How much of the medallion the emblem fills; above
     *   1.0 for small renders, where a tighter crop is what keeps it readable.
     * @return \GdImage The finished image, at $side x $side.
     * @throws \InvalidArgumentException When the slug has no artwork.
     */
    public function paint(string $slug, int $side, float $emblemScale = 1.0): GdImage
    {
        if (!isset(self::PALETTES[$slug])) {
            throw new InvalidArgumentException(sprintf('There is no artwork for "%s".', $slug));
        }

        $palette = self::PALETTES[$slug];
        $this->metal = self::METALS[$palette['metal']];

        $this->size = $side * self::SUPERSAMPLE;
        $this->cx = $this->size / 2;
        $this->cy = $this->size / 2;

        $this->im = imagecreatetruecolor($this->size, $this->size);
        imagealphablending($this->im, false);
        imagesavealpha($this->im, true);
        // The transparent border carries the rim's colour rather than black, so
        // that scaling down blends the outermost pixels towards the rim instead
        // of ringing the medallion in a dark halo.
        imagefill($this->im, 0, 0, $this->colour(self::RIM['bottom'], 127));
        imagealphablending($this->im, true);

        $outer = $this->size / 2 * 0.99;
        $inner = $outer * 0.88;

        $this->discGradient($this->cx, $this->cy, $outer, self::RIM['top'], self::RIM['bottom']);
        $this->discGradient($this->cx, $this->cy, $outer * 0.925, $this->metal['dark'], $this->metal['edge']);
        $this->discGradient($this->cx, $this->cy, $inner, $palette['top'], $palette['bottom']);

        // Inside the medallion the frame is the inner disc, shrunk a little so
        // no emblem ever touches the rim.
        $this->unit = $inner * 0.80 * $emblemScale;

        $this->drawEmblem($slug);

        $out = imagecreatetruecolor($side, $side);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagecopyresampled($out, $this->im, 0, 0, 0, 0, $side, $side, $this->size, $this->size);
        imagedestroy($this->im);

        return $out;
    }

    /**
     * Dispatch to the drawing method for one emblem.
     *
     * @param string $slug Which emblem to draw.
     * @return void
     */
    private function drawEmblem(string $slug): void
    {
        match ($slug) {
            'dragon-crest' => $this->dragonCrest(),
            'crossed-swords' => $this->crossedSwords(),
            'knight-helm' => $this->knightHelm(),
            'castle-keep' => $this->castleKeep(),
            'war-axe' => $this->warAxe(),
            'dragon-claw' => $this->dragonClaw(),
            'ogre-skull' => $this->ogreSkull(),
            'crown-of-war' => $this->crownOfWar(),
            'tower-shield' => $this->towerShield(),
            'war-banner' => $this->warBanner(),
        };
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Emblems
    // ─────────────────────────────────────────────────────────────────────

    /**
     * A horned dragon's head in profile, facing left.
     *
     * @return void
     */
    private function dragonCrest(): void
    {
        // The far horn, drawn first and in shadow so it sits behind the skull.
        $this->poly([
            [0.14, -0.14], [0.34, -0.34], [0.58, -0.50], [0.76, -0.56],
            [0.64, -0.38], [0.44, -0.22], [0.26, -0.04],
        ], $this->metal['dark']);

        // Open jaws, drawn as two pieces with the dark of the throat between
        // them: a closed muzzle at this size just reads as a lump.
        $upperJaw = [
            [-0.90, -0.02], [-0.80, -0.20], [-0.56, -0.28], [-0.30, -0.36],
            [-0.06, -0.48], [0.12, -0.42], [0.34, -0.24], [0.44, 0.02],
            [0.34, 0.20], [0.06, 0.16], [-0.22, 0.08], [-0.48, 0.02],
            [-0.72, 0.06],
        ];
        $this->poly($upperJaw, $this->metal['mid']);
        // Lit along the top of the muzzle and the brow, which is where the
        // light in every other emblem comes from too.
        $this->poly([
            [-0.90, -0.02], [-0.80, -0.20], [-0.56, -0.28], [-0.30, -0.36],
            [-0.06, -0.48], [0.12, -0.42], [0.20, -0.34], [-0.04, -0.36],
            [-0.30, -0.24], [-0.58, -0.14], [-0.80, -0.06],
        ], $this->metal['light']);

        $lowerJaw = [
            [-0.76, 0.18], [-0.46, 0.22], [-0.14, 0.30], [0.16, 0.38],
            [0.36, 0.46], [0.28, 0.60], [-0.02, 0.54], [-0.34, 0.44],
            [-0.64, 0.34], [-0.80, 0.28],
        ];
        $this->poly($lowerJaw, $this->metal['dark']);
        $this->poly([
            [-0.76, 0.18], [-0.46, 0.22], [-0.14, 0.30], [0.16, 0.38],
            [0.36, 0.46], [0.30, 0.52], [0.10, 0.44], [-0.20, 0.36],
            [-0.50, 0.28], [-0.78, 0.24],
        ], $this->metal['mid']);

        // Fangs: down from the upper jaw, up from the lower.
        foreach ([[-0.66, 0.05], [-0.40, 0.02], [-0.14, 0.08]] as [$x, $y]) {
            $this->poly([
                [$x - 0.055, $y], [$x + 0.055, $y], [$x, $y + 0.18],
            ], $this->metal['light']);
        }
        foreach ([[-0.58, 0.24], [-0.30, 0.30]] as [$x, $y]) {
            $this->poly([
                [$x - 0.05, $y], [$x + 0.05, $y], [$x, $y - 0.15],
            ], $this->metal['light']);
        }

        // Brow, eye socket and the slit pupil inside it.
        $this->poly([
            [-0.14, -0.30], [0.12, -0.24], [0.14, -0.06], [-0.10, -0.12],
        ], $this->metal['edge']);
        $this->poly([
            [-0.08, -0.25], [0.08, -0.20], [0.09, -0.09], [-0.06, -0.14],
        ], [0xF6, 0xC6, 0x3A]);
        $this->poly([
            [-0.01, -0.23], [0.03, -0.22], [0.04, -0.11], [0.00, -0.12],
        ], $this->metal['edge']);

        // Nostril.
        $this->poly([
            [-0.74, -0.14], [-0.62, -0.18], [-0.58, -0.09], [-0.70, -0.06],
        ], $this->metal['edge']);

        // The near horn, sweeping back over the neck.
        $this->poly([
            [0.04, -0.48], [0.26, -0.64], [0.50, -0.76], [0.74, -0.82],
            [0.60, -0.60], [0.42, -0.44], [0.24, -0.24], [0.10, -0.18],
        ], $this->metal['mid']);
        // Only the upper edge is lit: with the whole horn in the light tone it
        // merges into the top of the skull, which is lit too.
        $this->poly([
            [0.26, -0.64], [0.50, -0.76], [0.74, -0.82], [0.56, -0.68],
            [0.34, -0.54], [0.16, -0.42],
        ], $this->metal['light']);
    }

    /**
     * Two swords crossed over the field.
     *
     * @return void
     */
    private function crossedSwords(): void
    {
        $this->sword(-34.0, 0.0, 0.06, 0.94, true);
        $this->sword(34.0, 0.0, 0.06, 0.94, false);

        // A small boss where the blades meet, so the crossing reads as one
        // object rather than two overlapping ones.
        $this->disc(0.0, 0.06, 0.11, $this->metal['dark']);
        $this->disc(0.0, 0.06, 0.075, $this->metal['mid']);
        $this->disc(-0.015, 0.045, 0.045, $this->metal['light']);
    }

    /**
     * A closed great helm with a crest.
     *
     * @return void
     */
    private function knightHelm(): void
    {
        // The plume, in crimson rather than metal: on a steel helm against a
        // steel-grey field it is the only thing carrying any colour, and
        // without it the emblem loses its silhouette at small sizes.
        $plume = [0xC4, 0x2B, 0x33];
        $plumeLight = [0xF0, 0x5F, 0x52];
        $this->poly([
            [-0.16, -0.40], [-0.10, -0.72], [-0.02, -1.00], [0.08, -0.98],
            [0.10, -0.66], [0.16, -0.40],
        ], $plume);
        $this->poly([
            [-0.10, -0.42], [-0.06, -0.72], [-0.01, -0.98], [0.03, -0.98],
            [0.02, -0.68], [0.03, -0.42],
        ], $plumeLight);

        $helm = [
            [-0.38, -0.40], [-0.30, -0.52], [0.30, -0.52], [0.38, -0.40],
            [0.44, -0.12], [0.42, 0.18], [0.34, 0.46], [0.18, 0.64],
            [0.00, 0.70], [-0.18, 0.64], [-0.34, 0.46], [-0.42, 0.18],
            [-0.44, -0.12],
        ];
        $this->poly($helm, $this->metal['mid']);

        // Left half lit, right half left in the mid tone: a single hard
        // shading split suits the flat, heraldic look better than a gradient.
        $this->poly([
            [-0.38, -0.40], [-0.30, -0.52], [0.00, -0.52], [0.00, 0.70],
            [-0.18, 0.64], [-0.34, 0.46], [-0.42, 0.18], [-0.44, -0.12],
        ], $this->metal['light']);
        $this->poly([
            [0.16, -0.50], [0.30, -0.52], [0.38, -0.40], [0.44, -0.12],
            [0.42, 0.18], [0.34, 0.46], [0.18, 0.64], [0.14, 0.52],
            [0.24, 0.36], [0.30, 0.10], [0.28, -0.20],
        ], $this->metal['dark']);

        // Eye slit, then the reinforcing rib laid back over it, which is what
        // splits one slit into the two a great helm actually has.
        $this->poly([
            [-0.40, -0.16], [0.40, -0.16], [0.38, 0.00], [-0.38, 0.00],
        ], $this->metal['edge']);
        $this->poly([
            [-0.06, -0.52], [0.06, -0.52], [0.05, 0.66], [-0.05, 0.66],
        ], $this->metal['light']);

        // Breath holes.
        foreach ([-0.22, -0.08, 0.06, 0.20] as $x) {
            foreach ([0.18, 0.34] as $y) {
                $this->disc($x, $y, 0.035, $this->metal['edge']);
            }
        }
    }

    /**
     * The three towers of a keep, with the gate open.
     *
     * @return void
     */
    private function castleKeep(): void
    {
        // Side towers first: the centre tower overlaps them.
        foreach ([-1, 1] as $side) {
            $x0 = $side < 0 ? -0.62 : 0.26;
            $this->crenellated($x0, $x0 + 0.36, -0.30, 0.62, 3);
            $this->poly([
                [$x0 + 0.13, 0.06], [$x0 + 0.23, 0.06],
                [$x0 + 0.23, 0.26], [$x0 + 0.13, 0.26],
            ], $this->metal['edge']);
        }

        // Curtain wall between them.
        $this->crenellated(-0.34, 0.34, 0.06, 0.62, 5);

        // Centre tower.
        $this->crenellated(-0.20, 0.20, -0.66, 0.62, 3);

        // Gate: a dark arch, squared below and capped with a half disc.
        $this->poly([
            [-0.11, 0.18], [0.11, 0.18], [0.11, 0.62], [-0.11, 0.62],
        ], $this->metal['edge']);
        $this->disc(0.0, 0.18, 0.11, $this->metal['edge']);

        // Window slit above the gate.
        $this->poly([
            [-0.05, -0.34], [0.05, -0.34], [0.05, -0.10], [-0.05, -0.10],
        ], $this->metal['edge']);

        // Ground line under the whole thing.
        $this->poly([
            [-0.78, 0.62], [0.78, 0.62], [0.78, 0.72], [-0.78, 0.72],
        ], $this->metal['dark']);
    }

    /**
     * A double-bladed axe.
     *
     * @return void
     */
    private function warAxe(): void
    {
        $wood = [0x6B, 0x43, 0x22];
        $woodDark = [0x3E, 0x25, 0x12];

        // Haft, with the grain split down the middle.
        $this->poly([
            [-0.07, -0.82], [0.07, -0.82], [0.07, 0.86], [-0.07, 0.86],
        ], $wood);
        $this->poly([
            [0.01, -0.82], [0.07, -0.82], [0.07, 0.86], [0.01, 0.86],
        ], $woodDark);

        // Butt spike and grip wrap.
        $this->poly([
            [-0.07, -0.82], [0.00, -0.98], [0.07, -0.82],
        ], $this->metal['mid']);
        foreach ([0.44, 0.60, 0.76] as $y) {
            $this->poly([
                [-0.09, $y], [0.09, $y], [0.09, $y + 0.07], [-0.09, $y + 0.07],
            ], $woodDark);
        }

        foreach ([-1, 1] as $side) {
            // Narrow where it grips the haft, flaring out to a long convex
            // cutting edge. Drawn as one bit and mirrored, which is what makes
            // it a double-bitted axe rather than a hatchet.
            $blade = [
                [0.07, -0.22], [0.26, -0.40], [0.50, -0.46], [0.68, -0.38],
                [0.82, -0.18], [0.86, 0.02], [0.80, 0.22], [0.64, 0.38],
                [0.42, 0.36], [0.22, 0.20], [0.07, 0.04],
            ];
            $this->poly($this->mirrored($blade, $side), $this->metal['mid']);

            // The ground edge, a lighter band following the outer arc. Kept
            // narrow: run it across the whole bit and the axe flattens into a
            // white bowtie with no form in it at all.
            $edge = [
                [0.68, -0.38], [0.82, -0.18], [0.86, 0.02], [0.80, 0.22],
                [0.64, 0.38], [0.56, 0.30], [0.70, 0.14], [0.76, 0.00],
                [0.72, -0.16], [0.60, -0.30],
            ];
            $this->poly($this->mirrored($edge, $side), $this->metal['light']);
        }

        // Collar holding the head on, drawn last so it laps over both blades.
        $this->poly([
            [-0.13, -0.34], [0.13, -0.34], [0.13, -0.10], [-0.13, -0.10],
        ], $this->metal['dark']);
        $this->poly([
            [-0.13, -0.34], [0.13, -0.34], [0.13, -0.28], [-0.13, -0.28],
        ], $this->metal['mid']);
    }

    /**
     * Three talons closing over the field.
     *
     * @return void
     */
    private function dragonClaw(): void
    {
        // Three gouges torn across the field, rather than a claw drawn from the
        // outside. A whole foot is unreadable once it is 16 pixels wide; the
        // marks it leaves keep their shape all the way down.
        $gash = [
            [0.03, -0.96], [0.13, -0.54], [0.18, -0.04], [0.13, 0.46],
            [0.00, 0.94], [-0.04, 0.46], [-0.08, -0.04], [-0.05, -0.54],
        ];

        $lanes = [
            [-0.46, 0.94],
            [0.02, 1.04],
            [0.48, 0.88],
        ];

        // Shadow pass first, offset down and across, so each gouge reads as
        // something torn open rather than painted on.
        foreach ($lanes as [$dx, $scale]) {
            $this->poly(
                $this->transform($gash, 17.0, $dx + 0.06, 0.05, $scale * 1.06),
                $this->metal['edge']
            );
        }

        foreach ($lanes as [$dx, $scale]) {
            $this->poly($this->transform($gash, 17.0, $dx, 0.0, $scale), $this->metal['mid']);
            // The torn lip on the leading side catches the light.
            $this->poly($this->transform([
                [0.03, -0.96], [0.13, -0.54], [0.18, -0.04], [0.13, 0.46],
                [0.00, 0.94], [0.02, 0.46], [0.06, -0.04], [0.02, -0.54],
            ], 17.0, $dx, 0.0, $scale), $this->metal['light']);
        }
    }

    /**
     * The horned skull of something large and dead.
     *
     * @return void
     */
    private function ogreSkull(): void
    {
        // Horns, behind the skull, curling up and out.
        foreach ([-1, 1] as $side) {
            // Thick at the root and tapering the whole way to the tip: a horn
            // of even thickness reads as an ear at any size below about 64px.
            $horn = [
                [0.20, -0.52], [0.44, -0.64], [0.68, -0.80], [0.94, -0.98],
                [0.82, -0.70], [0.62, -0.50], [0.42, -0.30], [0.26, -0.16],
            ];
            $this->poly($this->mirrored($horn, $side), $this->metal['dark']);
            $this->poly($this->mirrored([
                [0.44, -0.64], [0.68, -0.80], [0.94, -0.98], [0.80, -0.76],
                [0.58, -0.58],
            ], $side), $this->metal['mid']);
            // Growth ridges.
            foreach ([[0.34, -0.50, 0.44, -0.36], [0.52, -0.62, 0.62, -0.50]] as $ridge) {
                $this->poly($this->mirrored([
                    [$ridge[0], $ridge[1]], [$ridge[0] + 0.05, $ridge[1] - 0.03],
                    [$ridge[2] + 0.05, $ridge[3] - 0.03], [$ridge[2], $ridge[3]],
                ], $side), $this->metal['edge']);
            }
        }

        // Cranium and cheekbones.
        $this->poly([
            [-0.30, -0.60], [0.30, -0.60], [0.52, -0.40], [0.58, -0.06],
            [0.46, 0.18], [0.22, 0.26], [-0.22, 0.26], [-0.46, 0.18],
            [-0.58, -0.06], [-0.52, -0.40],
        ], $this->metal['mid']);
        $this->poly([
            [-0.30, -0.60], [0.00, -0.60], [0.00, 0.26], [-0.22, 0.26],
            [-0.46, 0.18], [-0.58, -0.06], [-0.52, -0.40],
        ], $this->metal['light']);

        // Eye sockets, slanted inwards to look angry rather than surprised.
        foreach ([-1, 1] as $side) {
            $this->poly($this->mirrored([
                [0.08, -0.34], [0.40, -0.24], [0.40, 0.04], [0.12, -0.02],
            ], $side), $this->metal['edge']);
        }

        // Nasal cavity.
        $this->poly([
            [-0.09, 0.02], [0.09, 0.02], [0.00, 0.22],
        ], $this->metal['edge']);

        // Jaw and fangs.
        $this->poly([
            [-0.30, 0.26], [0.30, 0.26], [0.24, 0.62], [-0.24, 0.62],
        ], $this->metal['mid']);
        $this->poly([
            [-0.30, 0.26], [0.00, 0.26], [0.00, 0.62], [-0.24, 0.62],
        ], $this->metal['light']);
        foreach ([-0.20, -0.07, 0.06, 0.19] as $x) {
            $this->poly([
                [$x, 0.28], [$x + 0.10, 0.28], [$x + 0.05, 0.50],
            ], $this->metal['edge']);
        }

        // The two tusks that make it a monster and not a man.
        foreach ([-1, 1] as $side) {
            $this->poly($this->mirrored([
                [0.20, 0.24], [0.34, 0.26], [0.40, 0.62], [0.30, 0.68],
                [0.26, 0.44],
            ], $side), $this->metal['light']);
        }
    }

    /**
     * A crown riding above two crossed daggers.
     *
     * @return void
     */
    private function crownOfWar(): void
    {
        $this->sword(-40.0, 0.0, 0.40, 0.62, true);
        $this->sword(40.0, 0.0, 0.40, 0.62, false);

        $crown = [
            [-0.46, -0.10], [-0.46, -0.68], [-0.23, -0.38], [0.00, -0.82],
            [0.23, -0.38], [0.46, -0.68], [0.46, -0.10],
        ];
        $this->poly($crown, $this->metal['mid']);
        $this->poly([
            [-0.46, -0.10], [-0.46, -0.68], [-0.23, -0.38], [-0.12, -0.60],
            [-0.12, -0.10],
        ], $this->metal['light']);

        // Band across the base, with the rim of the circlet picked out.
        $this->poly([
            [-0.50, -0.14], [0.50, -0.14], [0.50, 0.06], [-0.50, 0.06],
        ], $this->metal['dark']);
        $this->poly([
            [-0.50, -0.14], [0.50, -0.14], [0.50, -0.08], [-0.50, -0.08],
        ], $this->metal['light']);

        // Gems: one on each point, three along the band.
        foreach ([[-0.46, -0.70], [0.00, -0.84], [0.46, -0.70]] as [$x, $y]) {
            $this->disc($x, $y, 0.075, $this->metal['light']);
        }
        foreach ([-0.26, 0.00, 0.26] as $x) {
            $this->disc($x, -0.03, 0.065, [0xD5, 0x2B, 0x3C]);
            $this->disc($x - 0.015, -0.045, 0.028, [0xFF, 0x93, 0x9C]);
        }
    }

    /**
     * A studded shield bearing a cross.
     *
     * @return void
     */
    private function towerShield(): void
    {
        $shield = [
            [-0.52, -0.64], [0.52, -0.64], [0.52, 0.00], [0.44, 0.30],
            [0.26, 0.58], [0.00, 0.78], [-0.26, 0.58], [-0.44, 0.30],
            [-0.52, 0.00],
        ];
        $this->poly($shield, $this->metal['mid']);
        $this->poly($this->scaled($shield, 0.84), $this->metal['edge']);
        $this->poly($this->scaled($shield, 0.76), $this->metal['dark']);

        // The cross, laid over the inner field.
        $this->poly([
            [-0.10, -0.52], [0.10, -0.52], [0.10, 0.56], [-0.10, 0.56],
        ], $this->metal['light']);
        $this->poly([
            [-0.36, -0.20], [0.36, -0.20], [0.36, 0.00], [-0.36, 0.00],
        ], $this->metal['light']);
        // Right arm of each bar in the mid tone, to keep the light on one side.
        $this->poly([
            [0.02, -0.52], [0.10, -0.52], [0.10, 0.56], [0.02, 0.56],
        ], $this->metal['mid']);
        $this->poly([
            [0.02, -0.20], [0.36, -0.20], [0.36, 0.00], [0.02, 0.00],
        ], $this->metal['mid']);

        // Rivets around the border.
        $studs = [
            [-0.44, -0.56], [0.00, -0.56], [0.44, -0.56],
            [-0.44, -0.16], [0.44, -0.16], [-0.44, 0.16], [0.44, 0.16],
            [-0.26, 0.46], [0.26, 0.46], [0.00, 0.66],
        ];
        foreach ($studs as [$x, $y]) {
            $this->disc($x, $y, 0.055, $this->metal['edge']);
            $this->disc($x - 0.008, $y - 0.008, 0.036, $this->metal['light']);
        }
    }

    /**
     * A pennant flying from a raised spear.
     *
     * @return void
     */
    private function warBanner(): void
    {
        $shaft = -0.38;

        // Spearhead.
        $this->poly([
            [$shaft, -0.98], [$shaft + 0.12, -0.74], [$shaft, -0.62],
            [$shaft - 0.12, -0.74],
        ], $this->metal['light']);
        $this->poly([
            [$shaft, -0.98], [$shaft + 0.12, -0.74], [$shaft, -0.62],
        ], $this->metal['mid']);

        // Shaft.
        $this->poly([
            [$shaft - 0.055, -0.68], [$shaft + 0.055, -0.68],
            [$shaft + 0.055, 0.88], [$shaft - 0.055, 0.88],
        ], [0x6B, 0x43, 0x22]);
        $this->poly([
            [$shaft + 0.015, -0.68], [$shaft + 0.055, -0.68],
            [$shaft + 0.055, 0.88], [$shaft + 0.015, 0.88],
        ], [0x3E, 0x25, 0x12]);

        // The cloth, notched into a swallow tail on the fly edge.
        $cloth = [
            [$shaft, -0.58], [0.78, -0.48], [0.56, -0.04], [0.80, 0.34],
            [$shaft, 0.30],
        ];
        $this->poly($cloth, $this->metal['mid']);
        // A fold: the lower half of the cloth turned away from the light.
        $this->poly([
            [$shaft, -0.02], [0.62, 0.06], [0.80, 0.34], [$shaft, 0.30],
        ], $this->metal['dark']);
        // Hem along the top edge.
        $this->poly([
            [$shaft, -0.58], [0.78, -0.48], [0.76, -0.38], [$shaft, -0.48],
        ], $this->metal['light']);

        // A lozenge charged on the cloth: the plainest heraldic mark there is,
        // and unlike a chevron it cannot be misread as a letter.
        $this->poly([
            [0.06, -0.38], [0.32, -0.06], [0.06, 0.22], [-0.20, -0.06],
        ], $this->metal['edge']);
        $this->poly([
            [0.06, -0.26], [0.24, -0.06], [0.06, 0.12], [-0.12, -0.06],
        ], $this->metal['light']);

        // Ties holding the cloth to the shaft.
        foreach ([-0.52, -0.20, 0.12] as $y) {
            $this->poly([
                [$shaft - 0.09, $y], [$shaft + 0.09, $y],
                [$shaft + 0.09, $y + 0.06], [$shaft - 0.09, $y + 0.06],
            ], $this->metal['edge']);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Shared shapes
    // ─────────────────────────────────────────────────────────────────────

    /**
     * A sword, pointing up before rotation.
     *
     * @param float $angle Degrees clockwise.
     * @param float $dx Where the hilt sits, horizontally.
     * @param float $dy Where the hilt sits, vertically.
     * @param float $scale Overall length.
     * @param bool $shaded Whether to draw it in shadow, for the blade behind.
     * @return void
     */
    private function sword(float $angle, float $dx, float $dy, float $scale, bool $shaded): void
    {
        $body = $shaded ? $this->metal['dark'] : $this->metal['mid'];
        $edge = $shaded ? $this->metal['mid'] : $this->metal['light'];

        $blade = [
            [0.00, -1.00], [0.085, -0.84], [0.095, 0.16], [-0.095, 0.16],
            [-0.085, -0.84],
        ];
        $this->poly($this->transform($blade, $angle, $dx, $dy, $scale), $body);
        $this->poly($this->transform([
            [0.00, -1.00], [0.085, -0.84], [0.095, 0.16], [0.02, 0.16],
            [0.02, -0.82],
        ], $angle, $dx, $dy, $scale), $edge);

        // Fuller: the groove down the middle of the blade.
        $this->poly($this->transform([
            [-0.028, -0.78], [0.028, -0.78], [0.028, 0.10], [-0.028, 0.10],
        ], $angle, $dx, $dy, $scale), $shaded ? $this->metal['edge'] : $this->metal['dark']);

        // Crossguard.
        $this->poly($this->transform([
            [-0.34, 0.18], [-0.29, 0.15], [0.29, 0.15], [0.34, 0.18],
            [0.34, 0.28], [0.29, 0.31], [-0.29, 0.31], [-0.34, 0.28],
        ], $angle, $dx, $dy, $scale), $shaded ? $this->metal['edge'] : $this->metal['dark']);

        // Grip and pommel.
        $this->poly($this->transform([
            [-0.055, 0.31], [0.055, 0.31], [0.05, 0.60], [-0.05, 0.60],
        ], $angle, $dx, $dy, $scale), $shaded ? $this->metal['edge'] : [0x6B, 0x43, 0x22]);
        // transform() works in the normalised frame, so the pommel goes through
        // disc() like every other shape rather than straight to canvas pixels.
        $pommel = $this->transform([[0.00, 0.66]], $angle, $dx, $dy, $scale)[0];
        $this->disc($pommel[0], $pommel[1], 0.085 * $scale, $body);
        $this->disc($pommel[0] - 0.02 * $scale, $pommel[1] - 0.02 * $scale, 0.045 * $scale, $edge);
    }

    /**
     * A wall or tower: a rectangle whose top edge is notched with merlons.
     *
     * @param float $x0 Left edge.
     * @param float $x1 Right edge.
     * @param float $top Top of the merlons.
     * @param float $bottom Base of the wall.
     * @param int $merlons How many teeth across the top.
     * @return void
     */
    private function crenellated(float $x0, float $x1, float $top, float $bottom, int $merlons): void
    {
        // A merlon and the gap beside it are the same width, and the row starts
        // and ends on a merlon, so the top edge has 2n-1 equal steps.
        $step = ($x1 - $x0) / (2 * $merlons - 1);
        $notch = $top + ($x1 - $x0) * 0.20;

        $points = [[$x0, $bottom], [$x0, $top]];
        for ($i = 0; $i < $merlons; $i++) {
            $left = $x0 + 2 * $i * $step;
            $points[] = [$left + $step, $top];
            if ($i < $merlons - 1) {
                $points[] = [$left + $step, $notch];
                $points[] = [$left + 2 * $step, $notch];
                $points[] = [$left + 2 * $step, $top];
            }
        }
        $points[] = [$x1, $bottom];

        $this->poly($points, $this->metal['mid']);

        // Lit face on the left third of the block.
        $width = $x1 - $x0;
        $this->poly([
            [$x0, $notch], [$x0 + $width * 0.30, $notch],
            [$x0 + $width * 0.30, $bottom], [$x0, $bottom],
        ], $this->metal['light']);
        $this->poly([
            [$x1 - $width * 0.22, $notch], [$x1, $notch],
            [$x1, $bottom], [$x1 - $width * 0.22, $bottom],
        ], $this->metal['dark']);

        // String course under the battlement.
        $this->poly([
            [$x0, $notch], [$x1, $notch], [$x1, $notch + $width * 0.09],
            [$x0, $notch + $width * 0.09],
        ], $this->metal['dark']);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Primitives
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Fill a polygon given in normalised coordinates.
     *
     * @param list<array{float, float}> $points The outline.
     * @param array{int, int, int} $rgb Fill colour.
     * @return void
     */
    private function poly(array $points, array $rgb): void
    {
        $flat = [];
        foreach ($points as [$x, $y]) {
            $flat[] = (int)round($this->cx + $x * $this->unit);
            $flat[] = (int)round($this->cy + $y * $this->unit);
        }

        imagefilledpolygon($this->im, $flat, $this->colour($rgb));
    }

    /**
     * Fill a circle given in normalised coordinates.
     *
     * @param float $x Centre.
     * @param float $y Centre.
     * @param float $r Radius.
     * @param array{int, int, int} $rgb Fill colour.
     * @return void
     */
    private function disc(float $x, float $y, float $r, array $rgb): void
    {
        $this->discAbsolute(
            $this->cx + $x * $this->unit,
            $this->cy + $y * $this->unit,
            $r * $this->unit,
            $rgb
        );
    }

    /**
     * Fill a circle given in canvas pixels.
     *
     * @param float $cx Centre.
     * @param float $cy Centre.
     * @param float $r Radius.
     * @param array{int, int, int} $rgb Fill colour.
     * @return void
     */
    private function discAbsolute(float $cx, float $cy, float $r, array $rgb): void
    {
        $d = (int)round($r * 2);
        imagefilledellipse($this->im, (int)round($cx), (int)round($cy), $d, $d, $this->colour($rgb));
    }

    /**
     * Fill a circle with a top-to-bottom gradient.
     *
     * Drawn scanline by scanline: GD has no gradient fill, and stepping down
     * the circle is both exact and fast enough at this size.
     *
     * @param float $cx Centre, in canvas pixels.
     * @param float $cy Centre, in canvas pixels.
     * @param float $r Radius, in canvas pixels.
     * @param array{int, int, int} $top Colour at the top of the circle.
     * @param array{int, int, int} $bottom Colour at the bottom.
     * @return void
     */
    private function discGradient(float $cx, float $cy, float $r, array $top, array $bottom): void
    {
        $first = (int)ceil($cy - $r);
        $last = (int)floor($cy + $r);

        for ($y = $first; $y <= $last; $y++) {
            $dy = $y - $cy;
            $halfWidth = sqrt(max(0.0, $r * $r - $dy * $dy));
            if ($halfWidth < 0.5) {
                continue;
            }

            $t = ($dy + $r) / (2 * $r);
            $colour = $this->colour([
                (int)round($top[0] + ($bottom[0] - $top[0]) * $t),
                (int)round($top[1] + ($bottom[1] - $top[1]) * $t),
                (int)round($top[2] + ($bottom[2] - $top[2]) * $t),
            ]);

            imageline(
                $this->im,
                (int)round($cx - $halfWidth),
                $y,
                (int)round($cx + $halfWidth),
                $y,
                $colour
            );
        }
    }

    /**
     * Allocate a colour on the working canvas.
     *
     * @param array{int, int, int} $rgb The colour.
     * @param int $alpha GD alpha, 0 opaque to 127 transparent.
     * @return int
     */
    private function colour(array $rgb, int $alpha = 0): int
    {
        return (int)imagecolorallocatealpha(
            $this->im,
            max(0, min(255, $rgb[0])),
            max(0, min(255, $rgb[1])),
            max(0, min(255, $rgb[2])),
            $alpha
        );
    }

    /**
     * Rotate, scale and move a list of points.
     *
     * @param list<array{float, float}> $points The shape.
     * @param float $angle Degrees clockwise.
     * @param float $dx Horizontal offset, applied after rotation.
     * @param float $dy Vertical offset, applied after rotation.
     * @param float $scale Uniform scale, applied before rotation.
     * @return list<array{float, float}>
     */
    private function transform(array $points, float $angle, float $dx, float $dy, float $scale): array
    {
        $radians = deg2rad($angle);
        $cos = cos($radians);
        $sin = sin($radians);

        $out = [];
        foreach ($points as [$x, $y]) {
            $x *= $scale;
            $y *= $scale;
            $out[] = [$x * $cos - $y * $sin + $dx, $x * $sin + $y * $cos + $dy];
        }

        return $out;
    }

    /**
     * Flip a shape across the vertical axis, or leave it alone.
     *
     * @param list<array{float, float}> $points The shape.
     * @param int $side 1 to keep, -1 to mirror.
     * @return list<array{float, float}>
     */
    private function mirrored(array $points, int $side): array
    {
        if ($side > 0) {
            return $points;
        }

        $out = [];
        foreach ($points as [$x, $y]) {
            $out[] = [-$x, $y];
        }

        return $out;
    }

    /**
     * Shrink a shape towards the origin.
     *
     * @param list<array{float, float}> $points The shape.
     * @param float $factor How much of its original size to keep.
     * @return list<array{float, float}>
     */
    private function scaled(array $points, float $factor): array
    {
        $out = [];
        foreach ($points as [$x, $y]) {
            $out[] = [$x * $factor, $y * $factor];
        }

        return $out;
    }

    /**
     * Sanity check that the painter and the picker agree on what exists.
     *
     * @return list<string> Slugs the service offers but this class cannot draw.
     */
    public static function missingArtwork(): array
    {
        return array_values(array_diff(array_keys(BrandingService::PRESETS), self::slugs()));
    }
}
