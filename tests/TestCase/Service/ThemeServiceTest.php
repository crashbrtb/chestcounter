<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\ThemeService;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;

/**
 * App\Service\ThemeService Test Case
 *
 * Half of this is about the stored choice. The other half reads the stylesheets
 * and checks the palettes themselves: a theme is only colours, and the way a
 * theme breaks is not an exception but text nobody can read. Contrast is the
 * one property of a palette that can be checked without eyes, so it is checked
 * here rather than left to whoever opens the page next.
 *
 * @uses \App\Service\ThemeService
 */
class ThemeServiceTest extends TestCase
{
    use LocatorAwareTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Config',
    ];

    /**
     * Pairs that carry body text, which WCAG AA puts at 4.5:1.
     *
     * @var list<array{string, string}>
     */
    private const TEXT_PAIRS = [
        ['--text', '--bg'],
        ['--text', '--card'],
        ['--text', '--surface'],
        ['--text-dark', '--card'],
        ['--text-soft', '--surface-sunken'],
        ['--text-muted-strong', '--surface'],
        ['--text', '--input-bg'],
        ['--text', '--surface-hover'],
        ['--accent-dark', '--accent-light'],
        ['--success-dark', '--success-light'],
        ['--warning-dark', '--warning-light'],
        ['--danger-dark', '--danger-light'],
        ['--info-dark', '--info-light'],
        // The epic-monster pill sits on a plain surface, not a tinted one.
        ['--epic-text', '--surface'],
    ];

    /**
     * Pairs that are not body text, with the bar each is held to.
     *
     * 3:1 is what WCAG asks of large text and of non-text indicators such as a
     * filled button. The two lower bars are deliberate:
     *
     *   - `--text-faint` is the label on a disabled control, and WCAG 1.4.3
     *     exempts inactive components from the contrast requirement. It still
     *     has to be visible, so it is held to 2:1 rather than waived.
     *   - `--line` is a hairline border, and a subtle one is the point: the
     *     default #e5e7eb on white is 1.24:1. The bar here only has to catch a
     *     theme whose border has ended up the same colour as its card.
     *
     * @var list<array{string, string, float}>
     */
    private const SECONDARY_PAIRS = [
        ['--muted', '--card', 3.0],
        ['--muted', '--bg', 3.0],
        ['--accent', '--card', 3.0],
        ['--link', '--card', 3.0],
        ['--epic', '--card', 3.0],
        ['--text-faint', '--surface-sunken', 2.0],
        ['--line', '--card', 1.15],
    ];

    /**
     * @var \App\Service\ThemeService
     */
    protected ThemeService $service;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ThemeService();
    }

    /**
     * Nothing chosen yet means the look the site has always had.
     *
     * @return void
     */
    public function testDefaultsToMorningLight(): void
    {
        $this->assertSame(ThemeService::DEFAULT_SLUG, $this->service->slug());
        $this->assertSame('morning-light', ThemeService::DEFAULT_SLUG);
    }

    /**
     * A choice is stored and read back.
     *
     * @return void
     */
    public function testChosenThemeIsStoredAndReadBack(): void
    {
        $this->service->select('twilight');

        $this->assertSame('twilight', $this->service->slug());
        $this->assertSame('Twilight', $this->service->current()['label']);
    }

    /**
     * The row is created on an install that predates the migration.
     *
     * @return void
     */
    public function testChoiceCreatesTheConfigRowWhenMissing(): void
    {
        $this->service->select('wildflowers');

        $row = $this->fetchTable('Config')
            ->find()
            ->where(['param' => ThemeService::PARAM])
            ->first();

        $this->assertNotNull($row);
        $this->assertSame('wildflowers', $row->value);
    }

    /**
     * An unknown theme is refused rather than written and then fallen back from
     * on every later page load.
     *
     * @return void
     */
    public function testUnknownThemeIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->select('neon-swamp');
    }

    /**
     * A value that is no longer a theme falls back instead of reaching the
     * page, where it would produce a data-theme nothing styles.
     *
     * @return void
     */
    public function testStoredValueThatIsNoLongerAThemeFallsBack(): void
    {
        $table = $this->fetchTable('Config');
        $row = $table->newEmptyEntity();
        $row->set('param', ThemeService::PARAM);
        $row->set('value', 'retired-theme');
        $row->set('description', 'left over from an older release');
        $table->saveOrFail($row);

        $this->assertSame(ThemeService::DEFAULT_SLUG, $this->service->slug());
    }

    /**
     * Every theme offered has a palette in the stylesheet, and the default one
     * is the palette on :root.
     *
     * @return void
     */
    public function testEveryThemeHasAPaletteInTheStylesheet(): void
    {
        $palettes = $this->palettes();

        foreach (array_keys(ThemeService::THEMES) as $slug) {
            $this->assertArrayHasKey($slug, $palettes, sprintf('No palette for "%s".', $slug));
        }
    }

    /**
     * Every palette defines the whole token set.
     *
     * A theme that forgets one token does not fail loudly: it inherits the
     * default value from :root, which on a dark theme means one white surface
     * in the middle of a dark page.
     *
     * @return void
     */
    public function testEveryPaletteDefinesTheWholeTokenSet(): void
    {
        $palettes = $this->palettes();
        $expected = array_keys($palettes[ThemeService::DEFAULT_SLUG]);

        foreach ($palettes as $slug => $palette) {
            $missing = array_diff($expected, array_keys($palette));
            $this->assertSame(
                [],
                array_values($missing),
                sprintf('Theme "%s" does not define: %s', $slug, implode(', ', $missing))
            );
        }
    }

    /**
     * Body text is readable on every surface it lands on, in every theme.
     *
     * @return void
     */
    public function testBodyTextMeetsContrastInEveryTheme(): void
    {
        foreach ($this->palettes() as $slug => $palette) {
            foreach (self::TEXT_PAIRS as [$ink, $ground]) {
                $ratio = $this->contrast($palette[$ink], $palette[$ground]);
                $this->assertGreaterThanOrEqual(
                    4.5,
                    $ratio,
                    sprintf(
                        '%s: %s (%s) on %s (%s) is only %.2f:1, and body text needs 4.5:1.',
                        $slug,
                        $ink,
                        $palette[$ink],
                        $ground,
                        $palette[$ground],
                        $ratio
                    )
                );
            }
        }
    }

    /**
     * Secondary text, accents and borders stay distinguishable.
     *
     * @return void
     */
    public function testSecondaryColoursMeetContrastInEveryTheme(): void
    {
        foreach ($this->palettes() as $slug => $palette) {
            foreach (self::SECONDARY_PAIRS as [$ink, $ground, $minimum]) {
                $ratio = $this->contrast($palette[$ink], $palette[$ground]);
                $this->assertGreaterThanOrEqual(
                    $minimum,
                    $ratio,
                    sprintf(
                        '%s: %s (%s) on %s (%s) is only %.2f:1, and needs %.1f:1.',
                        $slug,
                        $ink,
                        $palette[$ink],
                        $ground,
                        $palette[$ground],
                        $ratio,
                        $minimum
                    )
                );
            }
        }
    }

    /**
     * The swatches shown in the picker are the colours the theme actually uses.
     *
     * They are a hand-kept copy of four tokens, so this is the thing that
     * notices when the stylesheet moves on and the picker does not.
     *
     * @return void
     */
    public function testPickerSwatchesMatchTheStylesheet(): void
    {
        $palettes = $this->palettes();

        foreach (ThemeService::THEMES as $slug => $theme) {
            $palette = $palettes[$slug];
            foreach (['bg' => '--bg', 'card' => '--card', 'accent' => '--accent', 'text' => '--text-dark'] as $key => $token) {
                $this->assertSame(
                    strtolower($palette[$token]),
                    strtolower($theme['swatches'][$key]),
                    sprintf('Theme "%s" advertises a %s swatch the stylesheet does not use.', $slug, $key)
                );
            }
        }
    }

    /**
     * A theme is marked dark when its surfaces really are darker than its text.
     *
     * @return void
     */
    public function testDarkFlagMatchesThePalette(): void
    {
        $palettes = $this->palettes();

        foreach (ThemeService::THEMES as $slug => $theme) {
            $palette = $palettes[$slug];
            $groundIsDark = $this->luminance($palette['--bg']) < $this->luminance($palette['--text']);

            $this->assertSame(
                $theme['dark'],
                $groundIsDark,
                sprintf('Theme "%s" is flagged dark=%s but its palette says otherwise.', $slug, var_export($theme['dark'], true))
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────

    /**
     * Read every theme's tokens out of the stylesheets.
     *
     * The default lives on :root in theme-score.css, the rest in themes.css
     * under an exact `[data-theme="slug"]` selector; the AdminLTE compatibility
     * rules further down that file use compound selectors and are skipped.
     *
     * @return array<string, array<string, string>>
     */
    protected function palettes(): array
    {
        $base = (string)file_get_contents(WWW_ROOT . 'css' . DS . 'theme-score.css');
        $themes = (string)file_get_contents(WWW_ROOT . 'css' . DS . 'themes.css');

        $palettes = [];

        if (preg_match('/:root\s*\{(.*?)\}/s', $base, $match) === 1) {
            $palettes[ThemeService::DEFAULT_SLUG] = $this->tokens($match[1]);
        }

        if (preg_match_all('/\[data-theme="([a-z-]+)"\]\s*\{(.*?)\}/s', $themes, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $block) {
                $palettes[$block[1]] = $this->tokens($block[2]);
            }
        }

        return $palettes;
    }

    /**
     * Pull `--name: #hex;` declarations out of one block.
     *
     * @param string $block The body of a CSS rule.
     * @return array<string, string>
     */
    protected function tokens(string $block): array
    {
        preg_match_all('/(--[a-z-]+)\s*:\s*(#[0-9a-fA-F]{3,8})\s*;/', $block, $matches, PREG_SET_ORDER);

        $tokens = [];
        foreach ($matches as $match) {
            $tokens[$match[1]] = $match[2];
        }

        return $tokens;
    }

    /**
     * WCAG contrast ratio between two colours.
     *
     * @param string $one A hex colour.
     * @param string $two A hex colour.
     * @return float Between 1.0 and 21.0.
     */
    protected function contrast(string $one, string $two): float
    {
        $a = $this->luminance($one);
        $b = $this->luminance($two);

        return (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
    }

    /**
     * WCAG relative luminance of a hex colour.
     *
     * @param string $hex A hex colour, long or short form.
     * @return float Between 0.0 and 1.0.
     */
    protected function luminance(string $hex): float
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $channels = [];
        foreach ([0, 2, 4] as $offset) {
            $value = hexdec(substr($hex, $offset, 2)) / 255;
            $channels[] = $value <= 0.03928
                ? $value / 12.92
                : (($value + 0.055) / 1.055) ** 2.4;
        }

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }
}
