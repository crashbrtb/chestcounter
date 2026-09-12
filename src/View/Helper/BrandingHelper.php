<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Service\BrandingService;
use Cake\View\Helper;
use Throwable;

/**
 * Puts the chosen logo and favicon into the layout.
 *
 * Both layouts render on every request, including the error page shown when the
 * database is unreachable, so every method here falls back to something
 * printable rather than letting a failure escape and replace the real error
 * with a broken one.
 *
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \Cake\View\Helper\UrlHelper $Url
 */
class BrandingHelper extends Helper
{
    /**
     * @var array<int, string>
     */
    protected array $helpers = ['Html', 'Url'];

    /**
     * Resolved once per request: the layout asks for the logo and the icons
     * separately, and each answer costs a `config` read.
     */
    protected ?BrandingService $service = null;

    /**
     * The navbar brand image.
     *
     * @param array<string, mixed> $options Passed through to HtmlHelper::image().
     * @return string
     */
    public function logo(array $options = []): string
    {
        $options += ['class' => 'brand-image', 'alt' => ''];

        return $this->Html->image($this->logoUrl(), $options);
    }

    /**
     * URL of the chosen logo.
     *
     * @return string
     */
    public function logoUrl(): string
    {
        try {
            return $this->service()->logoUrl();
        } catch (Throwable $e) {
            return BrandingService::urlFor(BrandingService::DEFAULT_SLUG) . 'logo.png';
        }
    }

    /**
     * The `<link rel="icon">` tags for the chosen favicon.
     *
     * These replace HtmlHelper::meta('icon'), which can only ever point at
     * webroot/favicon.ico and knows nothing about the choice.
     *
     * @return string
     */
    public function favicon(): string
    {
        try {
            $service = $this->service();
            $links = $service->faviconLinks();
            // Safari on iOS ignores rel="icon" and wants this one for the home
            // screen; the 256px PNG is the closest thing we have to its 180px.
            $apple = $service->previewUrl($service->faviconSlug(), 'favicon');
        } catch (Throwable $e) {
            $links = [];
            $apple = null;
        }

        if ($links === []) {
            // The root file is kept in step with the choice, so it is the right
            // thing to fall back to when the chosen directory cannot be read.
            return $this->Html->meta('icon');
        }

        $out = '';
        foreach ($links as $link) {
            $out .= sprintf(
                '<link rel="icon" type="%s" sizes="%s" href="%s"/>',
                h($link['type']),
                h($link['sizes']),
                h($this->Url->assetUrl($link['href']))
            ) . "\n";
        }

        if ($apple !== null && str_contains($apple, '.png')) {
            $out .= sprintf(
                '<link rel="apple-touch-icon" href="%s"/>',
                h($this->Url->assetUrl($apple))
            ) . "\n";
        }

        return $out;
    }

    /**
     * The service, built on first use.
     *
     * @return \App\Service\BrandingService
     */
    protected function service(): BrandingService
    {
        return $this->service ??= new BrandingService();
    }
}
