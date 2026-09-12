<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Middleware\LocaleMiddleware;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;

/**
 * @uses \App\Middleware\LocaleMiddleware
 */
class LocaleMiddlewareTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Configure::write('I18n.languages', [
            'en_US' => 'English',
            'pt_BR' => 'Português',
        ]);
    }

    public static function acceptLanguageProvider(): array
    {
        return [
            'exact match' => ['pt-BR,pt;q=0.9,en;q=0.8', 'pt_BR'],
            'underscore form' => ['pt_BR', 'pt_BR'],
            'bare language falls back to its regional catalogue' => ['pt', 'pt_BR'],
            'other portuguese region still gets pt_BR' => ['pt-PT,pt;q=0.9', 'pt_BR'],
            'english region variant' => ['en-GB,en;q=0.9', 'en_US'],
            'quality decides, not header order' => ['es-ES;q=0.5,pt-BR;q=0.9', 'pt_BR'],
            'unsupported language keeps the default' => ['ja-JP,ja;q=0.9', null],
            'zero quality is ignored' => ['pt-BR;q=0,ja', null],
            'wildcard keeps the default' => ['*', null],
            'empty header keeps the default' => ['', null],
        ];
    }

    /**
     * @dataProvider acceptLanguageProvider
     */
    public function testNegotiate(string $header, ?string $expected): void
    {
        $this->assertSame($expected, LocaleMiddleware::negotiate($header));
    }

    public function testIsSupported(): void
    {
        $this->assertTrue(LocaleMiddleware::isSupported('pt_BR'));
        $this->assertTrue(LocaleMiddleware::isSupported('en_US'));
        $this->assertFalse(LocaleMiddleware::isSupported('ja_JP'));
        $this->assertFalse(LocaleMiddleware::isSupported('pt-BR'));
        $this->assertFalse(LocaleMiddleware::isSupported(null));
        $this->assertFalse(LocaleMiddleware::isSupported(['pt_BR']));
    }

    public function testSupportedLocales(): void
    {
        $this->assertSame(['en_US', 'pt_BR'], LocaleMiddleware::supportedLocales());
    }

    /**
     * The cases above run against a trimmed roster; this one uses the roster the
     * application actually ships, because that is what visitors hit.
     *
     * @dataProvider shippedRosterProvider
     */
    public function testNegotiateAgainstTheShippedRoster(string $header, string $expected): void
    {
        Configure::load('app', 'default', false);

        $this->assertSame($expected, LocaleMiddleware::negotiate($header));
    }

    public static function shippedRosterProvider(): array
    {
        return [
            // A bare language tag has to reach the regional catalogue we ship.
            'bare zh' => ['zh', 'zh_CN'],
            'bare ja' => ['ja', 'ja_JP'],
            'bare ko' => ['ko', 'ko_KR'],
            'bare ru' => ['ru', 'ru_RU'],
            'bare de' => ['de', 'de_DE'],
            'traditional chinese falls back to simplified' => ['zh-TW', 'zh_CN'],
            'austrian german' => ['de-AT,de;q=0.9', 'de_DE'],
            'canadian french' => ['fr-CA,fr;q=0.9,en;q=0.8', 'fr_FR'],
            'brazilian portuguese' => ['pt-BR', 'pt_BR'],
            'european portuguese' => ['pt-PT', 'pt_BR'],
            'mexican spanish' => ['es-MX,es;q=0.9', 'es_ES'],
            'turkish' => ['tr-TR,tr;q=0.9', 'tr_TR'],
            'italian' => ['it-IT,it;q=0.9', 'it_IT'],
            'korean over english' => ['ko-KR,ko;q=0.9,en;q=0.5', 'ko_KR'],
        ];
    }
}
