<?php
declare(strict_types=1);

namespace App\Service\Branding;

use GdImage;
use InvalidArgumentException;

/**
 * Builds a Windows .ico file out of a GD image.
 *
 * GD can read and write every raster format this application accepts but it
 * cannot write .ico, and browsers still ask for /favicon.ico by name before
 * they have seen a single line of our HTML. So the bytes are assembled here.
 *
 * Each entry is stored as an embedded PNG rather than the older BMP payload.
 * That is understood by every browser from IE11 and Vista onwards, keeps the
 * alpha channel intact without the separate AND mask a BMP entry needs, and
 * produces a file small enough to sit in webroot without a second thought.
 */
final class IcoWriter
{
    /**
     * The sizes packed into a generated icon.
     *
     * 16 and 32 are what a browser tab and a bookmark bar actually use; 48 and
     * 64 are there for the Windows taskbar and for pinned-site tiles, which
     * otherwise upscale the 32 and look soft.
     *
     * @var list<int>
     */
    public const DEFAULT_SIZES = [16, 32, 48, 64];

    /**
     * Encode an image as a multi-resolution .ico.
     *
     * @param \GdImage $source The artwork, ideally square and no smaller than the largest size.
     * @param list<int> $sizes Square sizes to pack, in pixels.
     * @return string The .ico bytes.
     * @throws \InvalidArgumentException When no usable size was given.
     */
    public static function fromImage(GdImage $source, array $sizes = self::DEFAULT_SIZES): string
    {
        $sizes = array_values(array_unique(array_filter(
            array_map('intval', $sizes),
            static fn (int $size): bool => $size > 0 && $size <= 256
        )));
        sort($sizes);

        if ($sizes === []) {
            throw new InvalidArgumentException('An icon needs at least one size between 1 and 256 pixels.');
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        $images = [];
        foreach ($sizes as $size) {
            $canvas = imagecreatetruecolor($size, $size);
            // Turn blending off before the fill and the copy so the source's
            // transparency is written through verbatim instead of being
            // composited onto an opaque black canvas.
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagefill($canvas, 0, 0, (int)imagecolorallocatealpha($canvas, 0, 0, 0, 127));
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $size, $size, $sourceWidth, $sourceHeight);

            ob_start();
            imagepng($canvas, null, 9);
            $png = (string)ob_get_clean();
            imagedestroy($canvas);

            $images[] = [$size, $png];
        }

        $count = count($images);
        $directory = '';
        $payload = '';
        // ICONDIR is 6 bytes, then one 16-byte ICONDIRENTRY per image; the
        // first payload byte lands immediately after that block.
        $offset = 6 + 16 * $count;

        foreach ($images as [$size, $png]) {
            $length = strlen($png);
            $directory .= pack(
                'CCCCvvVV',
                $size >= 256 ? 0 : $size,
                $size >= 256 ? 0 : $size,
                0,
                0,
                1,
                32,
                $length,
                $offset
            );
            $offset += $length;
            $payload .= $png;
        }

        return pack('vvv', 0, 1, $count) . $directory . $payload;
    }

    /**
     * Read the square side of the largest image declared by an .ico file.
     *
     * Used to check an uploaded icon, which GD cannot open at all. Only the
     * directory is parsed: it carries the dimensions, so the payload never has
     * to be trusted or decoded.
     *
     * @param string $bytes The raw file.
     * @return array{0: int, 1: int}|null Width and height, or null if this is not a readable .ico.
     */
    public static function readLargestSize(string $bytes): ?array
    {
        if (strlen($bytes) < 22) {
            return null;
        }

        $header = unpack('vreserved/vtype/vcount', substr($bytes, 0, 6));
        if ($header === false || $header['reserved'] !== 0 || $header['type'] !== 1 || $header['count'] < 1) {
            return null;
        }

        if (strlen($bytes) < 6 + 16 * $header['count']) {
            return null;
        }

        $best = null;
        for ($i = 0; $i < $header['count']; $i++) {
            $entry = unpack('Cwidth/Cheight', substr($bytes, 6 + 16 * $i, 2));
            if ($entry === false) {
                return null;
            }

            // A zero in either byte is how the format spells 256.
            $width = $entry['width'] === 0 ? 256 : $entry['width'];
            $height = $entry['height'] === 0 ? 256 : $entry['height'];

            if ($best === null || $width * $height > $best[0] * $best[1]) {
                $best = [$width, $height];
            }
        }

        return $best;
    }
}
