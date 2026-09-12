<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Middleware\LocaleMiddleware;
use Cake\I18n\I18n;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @uses \App\Controller\LocaleController
 */
class LocaleControllerTest extends TestCase
{
    use IntegrationTestTrait;

    public function tearDown(): void
    {
        I18n::setLocale('en_US');
        parent::tearDown();
    }

    public function testChangeStoresChoiceAndRedirectsBack(): void
    {
        // Cake only honours a Referer that is absolute and on this host.
        $this->configRequest(['headers' => ['Referer' => 'http://localhost/score']]);
        $this->get('/lang/pt_BR');

        $this->assertRedirect('/score');
        $this->assertSession('pt_BR', LocaleMiddleware::SESSION_KEY);
        $this->assertCookie('pt_BR', LocaleMiddleware::COOKIE_NAME);
    }

    public function testChangeIsReachableWithoutLogin(): void
    {
        $this->get('/lang/pt_BR');
        $this->assertRedirectNotContains('/users/login');
    }

    public function testChangeIgnoresUnsupportedLocale(): void
    {
        $this->get('/lang/xx_XX');

        $this->assertRedirect();
        $this->assertSession(null, LocaleMiddleware::SESSION_KEY);
    }

    public function testStoredChoiceSurvivesTheNextRequest(): void
    {
        $this->session([LocaleMiddleware::SESSION_KEY => 'pt_BR']);
        $this->get('/lang/pt_BR');

        $this->assertSame('pt_BR', I18n::getLocale());
    }

    public function testBrowserLanguageIsUsedWhenNothingWasChosen(): void
    {
        $this->configRequest(['headers' => ['Accept-Language' => 'pt-BR,pt;q=0.9,en;q=0.8']]);
        $this->get('/lang/');

        $this->assertSame('pt_BR', I18n::getLocale());
    }
}
