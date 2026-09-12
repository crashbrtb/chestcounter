<?php
declare(strict_types=1);

namespace App\Controller;

use App\Middleware\LocaleMiddleware;
use Cake\Http\Cookie\Cookie;
use Cake\I18n\I18n;
use DateTime;

/**
 * Language switcher.
 *
 * Lives in its own controller so the public score page can offer the switch
 * without sending anonymous visitors through the login screen.
 */
class LocaleController extends AppController
{
    /**
     * How long the remembered language survives without a session.
     */
    protected const COOKIE_LIFETIME = '+1 year';

    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Authentication->addUnauthenticatedActions(['change']);
    }

    /**
     * Stores the visitor's language choice and returns them to where they were.
     *
     * @param string|null $locale Locale code, e.g. `pt_BR`.
     * @return \Cake\Http\Response
     */
    public function change(?string $locale = null)
    {
        $response = $this->redirect($this->referer('/', true));

        if (!LocaleMiddleware::isSupported($locale)) {
            return $response;
        }

        I18n::setLocale($locale);
        $this->request->getSession()->write(LocaleMiddleware::SESSION_KEY, $locale);

        return $response->withCookie(
            Cookie::create(LocaleMiddleware::COOKIE_NAME, $locale, [
                'expires' => new DateTime(static::COOKIE_LIFETIME),
                'path' => $this->request->getAttribute('webroot'),
                'httponly' => true,
                'samesite' => 'Lax',
            ])
        );
    }
}
