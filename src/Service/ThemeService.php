<?php
declare(strict_types=1);

namespace App\Service;

use Cake\ORM\Locator\LocatorAwareTrait;
use InvalidArgumentException;
use Throwable;

/**
 * Which colour scheme the site is wearing.
 *
 * A theme is nothing but a palette. `webroot/css/theme-score.css` declares the
 * whole token set on :root and paints every surface, border and neutral text
 * colour through it; `webroot/css/themes.css` redefines those same tokens under
 * a `[data-theme]` selector. So all this class has to do is decide which value
 * goes on the `<html>` element, and store the administrator's choice.
 *
 * The swatches below exist so the picker can show what a theme looks like
 * without loading it. They are copies of four tokens from the stylesheet and
 * have to be kept in step with it by hand; that is the price of letting the
 * picker render a preview of a theme that is not currently applied.
 */
class ThemeService
{
    use LocatorAwareTrait;

    /**
     * `config` row holding the chosen theme.
     */
    public const PARAM = 'site_theme';

    /**
     * What the site wears when nothing has been chosen.
     */
    public const DEFAULT_SLUG = 'morning-light';

    /**
     * The themes on offer, in the order the picker shows them.
     *
     * `swatches` are bg, card, accent and text, which is enough for the picker
     * to draw a small mock of a page in that theme.
     *
     * @var array<string, array{label: string, blurb: string, dark: bool, swatches: array{bg: string, card: string, accent: string, text: string}}>
     */
    public const THEMES = [
        'morning-light' => [
            'label' => 'Morning Light',
            'blurb' => 'Cool white, indigo and plenty of air. The original look.',
            'dark' => false,
            'swatches' => [
                'bg' => '#f5f7fb',
                'card' => '#ffffff',
                'accent' => '#4f46e5',
                'text' => '#111827',
            ],
        ],
        'deepest-dark' => [
            'label' => 'Deepest Dark',
            'blurb' => 'Near black and high contrast, for long nights counting chests.',
            'dark' => true,
            'swatches' => [
                'bg' => '#0b0e14',
                'card' => '#141a24',
                'accent' => '#7c8cff',
                'text' => '#f3f7fc',
            ],
        ],
        'wildflowers' => [
            'label' => 'Wildflowers',
            'blurb' => 'Warm paper and poppy pink, with a meadow of colour behind it.',
            'dark' => false,
            'swatches' => [
                'bg' => '#fdf6e9',
                'card' => '#fffdf8',
                'accent' => '#c4457f',
                'text' => '#2a2118',
            ],
        ],
        'twilight' => [
            'label' => 'Twilight',
            'blurb' => 'The grey half hour after sunset: slate, dusk violet, a last amber.',
            'dark' => true,
            'swatches' => [
                'bg' => '#262b33',
                'card' => '#2f353f',
                'accent' => '#a885d8',
                'text' => '#eff2f6',
            ],
        ],
    ];

    /**
     * Whether a slug names a theme that exists.
     *
     * @param string $slug The slug to check.
     * @return bool
     */
    public static function isKnown(string $slug): bool
    {
        return array_key_exists($slug, self::THEMES);
    }

    /**
     * The theme the site is currently wearing.
     *
     * Falls back rather than throwing: this is read while the layout is being
     * built, which also happens for the error page shown when the database is
     * the thing that has gone wrong.
     *
     * @return string
     */
    public function slug(): string
    {
        try {
            $row = $this->fetchTable('Config')->find()->where(['param' => self::PARAM])->first();
        } catch (Throwable $e) {
            return self::DEFAULT_SLUG;
        }

        $value = $row->value ?? null;

        return is_string($value) && self::isKnown($value) ? $value : self::DEFAULT_SLUG;
    }

    /**
     * Details of the theme currently in use.
     *
     * @return array{label: string, blurb: string, dark: bool, swatches: array{bg: string, card: string, accent: string, text: string}}
     */
    public function current(): array
    {
        return self::THEMES[$this->slug()];
    }

    /**
     * Change the site's theme.
     *
     * @param string $slug One of the slugs in THEMES.
     * @return void
     * @throws \InvalidArgumentException When there is no such theme.
     */
    public function select(string $slug): void
    {
        if (!self::isKnown($slug)) {
            throw new InvalidArgumentException(sprintf('There is no theme called "%s".', $slug));
        }

        $table = $this->fetchTable('Config');
        $row = $table->find()->where(['param' => self::PARAM])->first();

        if ($row === null) {
            $row = $table->newEmptyEntity();
            $row->set('param', self::PARAM);
            $row->set('description', 'Colour scheme for the whole site. Change it under Admin > Theme.');
        }

        $row->set('value', $slug);
        $table->saveOrFail($row);
    }
}
