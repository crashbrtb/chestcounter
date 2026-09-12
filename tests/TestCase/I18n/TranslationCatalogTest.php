<?php
declare(strict_types=1);

namespace App\Test\TestCase\I18n;

use App\Middleware\LocaleMiddleware;
use Cake\I18n\I18n;
use Cake\TestSuite\TestCase;

/**
 * Guards the catalogues themselves: a malformed .po does not raise anything,
 * it just silently serves English, which is exactly the failure we cannot see.
 */
class TranslationCatalogTest extends TestCase
{
    /**
     * The source language: its msgids are the translation, so it ships no .po.
     */
    private const SOURCE_LOCALE = 'en_US';

    /**
     * One string per locale, picked because it is short and unmistakable.
     *
     * @var array<string, string>
     */
    private const SAMPLES = [
        'pt_BR' => 'Baús Coletados',
        'es_ES' => 'Cofres Recogidos',
        'fr_FR' => 'Coffres collectés',
        'de_DE' => 'Gesammelte Truhen',
        'it_IT' => 'Forzieri Raccolti',
        'tr_TR' => 'Toplanan Sandıklar',
        'ru_RU' => 'Собранные сундуки',
        'zh_CN' => '收集的宝箱',
        'ja_JP' => '回収した宝箱',
        'ko_KR' => '수집한 상자',
    ];

    public function tearDown(): void
    {
        I18n::setLocale(self::SOURCE_LOCALE);
        parent::tearDown();
    }

    public function testEveryConfiguredLocaleHasACatalogue(): void
    {
        $configured = LocaleMiddleware::supportedLocales();
        $this->assertContains(self::SOURCE_LOCALE, $configured);

        foreach ($configured as $locale) {
            if ($locale === self::SOURCE_LOCALE) {
                continue;
            }
            $this->assertFileExists(
                RESOURCES . 'locales' . DS . $locale . DS . 'default.po',
                "Missing catalogue for configured locale {$locale}"
            );
        }
    }

    public function testEveryTranslatedLocaleIsConfigured(): void
    {
        // A catalogue nobody can select is dead weight, and usually a typo.
        $configured = LocaleMiddleware::supportedLocales();
        foreach (glob(RESOURCES . 'locales' . DS . '*' . DS . 'default.po') as $catalogue) {
            $locale = basename(dirname($catalogue));
            $this->assertContains($locale, $configured, "Catalogue {$locale} is not in I18n.languages");
        }
    }

    /**
     * @dataProvider sampleProvider
     */
    public function testCatalogueIsLoaded(string $locale, string $expected): void
    {
        I18n::setLocale($locale);

        $this->assertSame($expected, __('Collected Chests'));
    }

    public static function sampleProvider(): array
    {
        $cases = [];
        foreach (self::SAMPLES as $locale => $expected) {
            $cases[$locale] = [$locale, $expected];
        }

        return $cases;
    }

    public function testEverySampleLocaleIsCovered(): void
    {
        $translated = array_values(array_diff(LocaleMiddleware::supportedLocales(), [self::SOURCE_LOCALE]));
        sort($translated);
        $sampled = array_keys(self::SAMPLES);
        sort($sampled);

        $this->assertSame($translated, $sampled, 'SAMPLES has drifted from I18n.languages');
    }

    public function testPlaceholdersSurviveTranslation(): void
    {
        // A translation that drops {0} would render a message with a hole in it.
        foreach (LocaleMiddleware::supportedLocales() as $locale) {
            I18n::setLocale($locale);
            $this->assertStringContainsString(
                '7',
                __('Available balance: {0} $', 7),
                "Placeholder lost in {$locale}"
            );
        }
    }

    public function testUnknownStringFallsBackToEnglish(): void
    {
        I18n::setLocale('pt_BR');

        $this->assertSame('Not a real string', __('Not a real string'));
    }
}
