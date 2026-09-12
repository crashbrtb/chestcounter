<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Service\ThemeService;
use Cake\View\Helper;
use Throwable;

/**
 * Puts the chosen theme on the page.
 *
 * Named SiteTheme rather than Theme because CakePHP already uses "theme" for
 * the template-override mechanism the CakeLte plugin is built on, and two
 * different meanings of the word in one layout would be one too many.
 */
class SiteThemeHelper extends Helper
{
    /**
     * Resolved once per request: the layout asks for it in the <html> tag and
     * the picker asks again further down the page.
     */
    protected ?string $slug = null;

    /**
     * The value for the `data-theme` attribute on the root element.
     *
     * Every layout renders on every request, the error page included, so a
     * failure here must not be allowed to replace a real error with a broken
     * template.
     *
     * @return string
     */
    public function slug(): string
    {
        if ($this->slug !== null) {
            return $this->slug;
        }

        try {
            $this->slug = (new ThemeService())->slug();
        } catch (Throwable $e) {
            $this->slug = ThemeService::DEFAULT_SLUG;
        }

        return $this->slug;
    }

    /**
     * Whether the current theme has surfaces darker than its text.
     *
     * @return bool
     */
    public function isDark(): bool
    {
        return ThemeService::THEMES[$this->slug()]['dark'] ?? false;
    }
}
