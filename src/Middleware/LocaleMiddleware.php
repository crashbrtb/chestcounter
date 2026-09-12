<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Core\Configure;
use Cake\I18n\I18n;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Resolves the locale for every request.
 *
 * CakePHP only applies `I18n::setLocale()` for the request it runs in, so an
 * explicit choice has to be re-applied on each request or the site silently
 * snaps back to `App.defaultLocale`. Order of precedence:
 *
 *   1. the language the visitor picked (session, then the long-lived cookie)
 *   2. the browser's `Accept-Language` header
 *   3. `App.defaultLocale`
 *
 * The cookie exists because anonymous visitors on the public score page lose
 * their session long before they lose their browser profile.
 */
class LocaleMiddleware implements MiddlewareInterface
{
    /**
     * Session key holding the visitor's explicit choice.
     *
     * Kept as `Config.language` for backwards compatibility with sessions
     * written by the previous implementation.
     */
    public const SESSION_KEY = 'Config.language';

    /**
     * Cookie holding the same choice, for when the session is gone.
     */
    public const COOKIE_NAME = 'locale';

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $supported = static::supportedLocales();

        /** @var \Cake\Http\ServerRequest $request */
        $chosen = $request->getSession()->read(static::SESSION_KEY);
        if (!static::isSupported($chosen, $supported)) {
            $chosen = $request->getCookie(static::COOKIE_NAME);
        }

        $locale = static::isSupported($chosen, $supported)
            ? $chosen
            : static::negotiate($request->getHeaderLine('Accept-Language'), $supported);

        if ($locale !== null) {
            I18n::setLocale($locale);
        }

        return $handler->handle($request);
    }

    /**
     * The locales the application ships translations for.
     *
     * @return array<string> Locale codes, most preferred fallback first.
     */
    public static function supportedLocales(): array
    {
        return array_keys((array)Configure::read('I18n.languages', []));
    }

    /**
     * @param mixed $locale Candidate locale code.
     * @param array<string>|null $supported Allowed locales, read from config when omitted.
     * @return bool
     */
    public static function isSupported(mixed $locale, ?array $supported = null): bool
    {
        $supported ??= static::supportedLocales();

        return is_string($locale) && in_array($locale, $supported, true);
    }

    /**
     * Picks the best supported locale for an `Accept-Language` header.
     *
     * `Locale::acceptFromHttp()` alone is not enough: it returns only the
     * top-ranked entry, and a browser asking for plain `pt` would never match
     * our `pt_BR` catalogue. So we walk the header by quality and fall back to
     * matching on the primary language.
     *
     * @param string $header Raw `Accept-Language` header value.
     * @param array<string>|null $supported Allowed locales.
     * @return string|null Matching locale, or null to leave the default in place.
     */
    public static function negotiate(string $header, ?array $supported = null): ?string
    {
        $supported ??= static::supportedLocales();
        if (!$supported) {
            return null;
        }

        // language => locale, so `pt` can resolve to `pt_BR`.
        $byLanguage = [];
        foreach ($supported as $locale) {
            $language = strtolower(strtok($locale, '_'));
            $byLanguage[$language] ??= $locale;
        }

        foreach (static::parseAcceptLanguage($header) as $candidate) {
            if ($candidate === '*') {
                return null;
            }

            $normalized = str_replace('-', '_', $candidate);
            foreach ($supported as $locale) {
                if (strcasecmp($normalized, $locale) === 0) {
                    return $locale;
                }
            }

            $language = strtolower(strtok($normalized, '_'));
            if (isset($byLanguage[$language])) {
                return $byLanguage[$language];
            }
        }

        return null;
    }

    /**
     * Splits an `Accept-Language` header into tags ordered by quality.
     *
     * @param string $header Raw header value.
     * @return array<string> Language tags, most preferred first.
     */
    protected static function parseAcceptLanguage(string $header): array
    {
        $weighted = [];
        $order = 0;

        foreach (explode(',', $header) as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            $pieces = explode(';', $part);
            $tag = trim(array_shift($pieces));
            if ($tag === '') {
                continue;
            }

            $quality = 1.0;
            foreach ($pieces as $piece) {
                [$key, $value] = array_pad(explode('=', $piece, 2), 2, null);
                if (strtolower(trim((string)$key)) === 'q') {
                    $quality = (float)$value;
                }
            }

            if ($quality <= 0) {
                continue;
            }

            // The counter keeps the header's own order stable within a quality.
            $weighted[] = [$tag, $quality, $order++];
        }

        usort($weighted, fn (array $a, array $b) => [$b[1], $a[2]] <=> [$a[1], $b[2]]);

        return array_column($weighted, 0);
    }
}
