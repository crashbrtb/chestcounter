<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Service\ThemeService;
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Theme page of App\Controller\ConfigController.
 *
 * @uses \App\Controller\ConfigController::theme()
 */
class ConfigControllerThemeTest extends TestCase
{
    use IntegrationTestTrait;
    use LocatorAwareTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Config',
        'app.Users',
        'app.Roles',
        // User 1 holds role 1, which is what requireAdmin() looks for.
        'app.RolesUsers',
    ];

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->enableCsrfToken();
        $this->enableRetainFlashMessages();
    }

    /**
     * The theme is a site-wide change, so only administrators may make it.
     *
     * @return void
     */
    public function testPageIsClosedToNonAdministrators(): void
    {
        $this->signIn(2);

        $this->get('/config/theme');

        $this->assertResponseCode(403);
    }

    /**
     * All four themes are offered, each with its own preview.
     *
     * @return void
     */
    public function testPageOffersEveryTheme(): void
    {
        $this->signIn(1);

        $this->get('/config/theme');

        $this->assertResponseOk();

        foreach (ThemeService::THEMES as $slug => $theme) {
            $this->assertResponseContains(sprintf('value="%s"', $slug));
            $this->assertResponseContains(h($theme['label']));
            // The swatch is what the preview is painted from.
            $this->assertResponseContains($theme['swatches']['accent']);
        }
    }

    /**
     * Choosing a theme stores it and puts it on the next page rendered.
     *
     * @return void
     */
    public function testChosenThemeIsStoredAndApplied(): void
    {
        $this->signIn(1);

        $this->post('/config/theme', ['theme' => 'deepest-dark']);

        $this->assertRedirect(['action' => 'theme']);
        $this->assertFlashMessage('Theme updated.');
        $this->assertSame('deepest-dark', $this->storedTheme());

        // The layout is what actually carries the choice to the browser.
        $this->get('/config/theme');
        $this->assertResponseContains('data-theme="deepest-dark"');
    }

    /**
     * A theme that does not exist cannot be forced through by editing the form.
     *
     * @return void
     */
    public function testUnknownThemeIsRefused(): void
    {
        $this->signIn(1);
        $this->post('/config/theme', ['theme' => 'wildflowers']);

        $this->post('/config/theme', ['theme' => 'neon-swamp']);

        $this->assertResponseOk();
        $this->assertFlashElement('flash/error');
        $this->assertSame('wildflowers', $this->storedTheme());
    }

    // ─────────────────────────────────────────────────────────────────────

    /**
     * Put a user in the session.
     *
     * @param int $id The user id; 1 holds the admin role in the fixtures.
     * @return void
     */
    protected function signIn(int $id): void
    {
        $this->session([
            'Auth' => [
                'id' => $id,
                'username' => 'tester',
                'email' => 'tester@example.com',
                'created' => new DateTime('2026-01-01 00:00:00'),
            ],
        ]);
    }

    /**
     * Read the stored theme back out of the `config` table.
     *
     * @return string|null
     */
    protected function storedTheme(): ?string
    {
        $row = $this->fetchTable('Config')->find()->where(['param' => ThemeService::PARAM])->first();

        return $row->value ?? null;
    }
}
