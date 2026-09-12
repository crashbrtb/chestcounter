<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Service\Branding\IcoWriter;
use App\Service\BrandingService;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;

/**
 * Branding page of App\Controller\ConfigController.
 *
 * Anything that writes runs against a throwaway branding root, so a test run
 * cannot change the logo of the site it is sitting next to.
 *
 * @uses \App\Controller\ConfigController::branding()
 */
class ConfigControllerBrandingTest extends TestCase
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
     * The disposable branding root, when a test has asked for one.
     *
     * @var string|null
     */
    protected ?string $root = null;

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
     * @inheritDoc
     */
    public function tearDown(): void
    {
        Configure::delete('Branding.root');
        if ($this->root !== null) {
            $this->removeTree($this->root);
            $this->root = null;
        }

        parent::tearDown();
    }

    /**
     * The page is administrators only: it changes what every visitor sees.
     *
     * Anonymous visitors never reach the controller's own admin check, because
     * the authentication middleware sends them to the login page first.
     *
     * @return void
     */
    public function testPageIsClosedToAnonymousVisitors(): void
    {
        $this->get('/config/branding');

        $this->assertRedirectContains('/users/login');
    }

    /**
     * A signed-in user without the admin role is turned away.
     *
     * @return void
     */
    public function testPageIsClosedToNonAdministrators(): void
    {
        $this->session([
            'Auth' => [
                'id' => 2,
                'username' => 'member',
                'email' => 'member@example.com',
                'created' => new DateTime('2026-01-01 00:00:00'),
            ],
        ]);

        $this->get('/config/branding');

        $this->assertResponseCode(403);
    }

    /**
     * Every preset on offer is rendered, along with the rules for uploading.
     *
     * @return void
     */
    public function testPageOffersEveryPresetAndStatesTheRules(): void
    {
        $this->signInAsAdmin();

        $this->get('/config/branding');

        $this->assertResponseOk();

        foreach (BrandingService::PRESETS as $slug => $preset) {
            $this->assertResponseContains(sprintf('value="%s"', $slug));
            $this->assertResponseContains(h($preset['label']));
        }

        // The limits are stated on the page rather than only enforced on save.
        $this->assertResponseContains('128x128');
        $this->assertResponseContains('2048x2048');
        $this->assertResponseContains('2 MB');
        $this->assertResponseContains('512 KB');
        $this->assertResponseContains('PNG, JPEG, GIF or WebP');
    }

    /**
     * Choosing a preset writes the choice and refreshes the root favicon.
     *
     * @return void
     */
    public function testChoosingPresetsStoresBothSlots(): void
    {
        $this->givenDisposableRootWith(['war-axe', 'ogre-skull']);
        $this->signInAsAdmin();

        $this->post('/config/branding', [
            'logo' => 'war-axe',
            'favicon' => 'ogre-skull',
        ]);

        $this->assertRedirect(['action' => 'branding']);
        $this->assertFlashMessage('Branding updated.');

        $this->assertSame('war-axe', $this->storedChoice(BrandingService::PARAM_LOGO));
        $this->assertSame('ogre-skull', $this->storedChoice(BrandingService::PARAM_FAVICON));
        $this->assertFileExists(rtrim($this->root, DS) . DS . 'favicon.ico');
    }

    /**
     * Uploading is a way of choosing: the uploaded file becomes the selection
     * without the administrator having to also click its card.
     *
     * @return void
     */
    public function testUploadingSelectsTheUploadedArtwork(): void
    {
        $this->givenDisposableRootWith(['war-axe']);
        $this->signInAsAdmin();

        $this->post('/config/branding', [
            'logo' => 'war-axe',
            'logo_file' => $this->upload($this->png(400, 400), 'mine.png'),
        ]);

        $this->assertRedirect(['action' => 'branding']);
        $this->assertSame(BrandingService::CUSTOM, $this->storedChoice(BrandingService::PARAM_LOGO));
        $this->assertFileExists(
            BrandingService::directoryFor(BrandingService::CUSTOM) . 'logo.png'
        );
    }

    /**
     * A rejected upload explains itself and leaves the current choice standing.
     *
     * @return void
     */
    public function testRejectedUploadKeepsTheCurrentChoice(): void
    {
        $this->givenDisposableRootWith(['war-axe', 'ogre-skull']);
        $this->signInAsAdmin();

        $this->post('/config/branding', ['logo' => 'war-axe', 'favicon' => 'war-axe']);
        $this->assertSame('war-axe', $this->storedChoice(BrandingService::PARAM_LOGO));

        // Far too small, which is one of the rules printed on the form.
        $this->post('/config/branding', [
            'logo' => 'ogre-skull',
            'logo_file' => $this->upload($this->png(32, 32), 'tiny.png'),
        ]);

        $this->assertResponseOk();
        $this->assertFlashElement('flash/error');
        $this->assertSame('war-axe', $this->storedChoice(BrandingService::PARAM_LOGO));
        $this->assertFileDoesNotExist(
            BrandingService::directoryFor(BrandingService::CUSTOM) . 'logo.png'
        );
    }

    /**
     * A slug that is not on offer cannot be forced through by editing the form.
     *
     * @return void
     */
    public function testUnknownSlugIsRefused(): void
    {
        $this->givenDisposableRootWith(['war-axe']);
        $this->signInAsAdmin();

        $this->post('/config/branding', ['logo' => 'war-axe']);
        $this->post('/config/branding', ['logo' => '../../../etc/passwd']);

        $this->assertResponseOk();
        $this->assertSame('war-axe', $this->storedChoice(BrandingService::PARAM_LOGO));
    }

    // ─────────────────────────────────────────────────────────────────────

    /**
     * Sign in as a user who holds the admin role.
     *
     * @return void
     */
    protected function signInAsAdmin(): void
    {
        $this->session([
            'Auth' => [
                'id' => 1,
                'username' => 'tester',
                'email' => 'tester@example.com',
                'created' => new DateTime('2026-01-01 00:00:00'),
            ],
        ]);
    }

    /**
     * Point the service at a temporary root holding the given presets.
     *
     * @param list<string> $slugs Presets to create there.
     * @return void
     */
    protected function givenDisposableRootWith(array $slugs): void
    {
        $this->root = TMP . 'branding-controller-' . uniqid() . DS;
        mkdir($this->root, 0777, true);
        Configure::write('Branding.root', $this->root);

        $image = imagecreatetruecolor(16, 16);
        foreach ($slugs as $slug) {
            $directory = BrandingService::directoryFor($slug);
            mkdir($directory, 0777, true);

            ob_start();
            imagepng($image);
            $png = (string)ob_get_clean();

            file_put_contents($directory . 'logo.png', $png);
            file_put_contents($directory . 'favicon.png', $png);
            file_put_contents($directory . 'favicon.ico', IcoWriter::fromImage($image, [16]));
        }
        imagedestroy($image);
    }

    /**
     * Read a choice back out of the `config` table.
     *
     * @param string $param The row to read.
     * @return string|null
     */
    protected function storedChoice(string $param): ?string
    {
        $row = $this->fetchTable('Config')->find()->where(['param' => $param])->first();

        return $row->value ?? null;
    }

    /**
     * Build an upload out of raw bytes.
     *
     * @param string $bytes The file contents.
     * @param string $name The client-side file name.
     * @return \Laminas\Diactoros\UploadedFile
     */
    protected function upload(string $bytes, string $name): UploadedFile
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write($bytes);
        $stream->rewind();

        return new UploadedFile($stream, strlen($bytes), UPLOAD_ERR_OK, $name, 'image/png');
    }

    /**
     * A PNG of the given size.
     *
     * @param int $width Width in pixels.
     * @param int $height Height in pixels.
     * @return string
     */
    protected function png(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        ob_start();
        imagepng($image);
        $bytes = (string)ob_get_clean();
        imagedestroy($image);

        return $bytes;
    }

    /**
     * Delete a directory and everything under it.
     *
     * @param string $path The directory.
     * @return void
     */
    protected function removeTree(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        foreach (scandir($path) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $full = rtrim($path, DS) . DS . $entry;
            is_dir($full) ? $this->removeTree($full) : @unlink($full);
        }

        @rmdir($path);
    }
}
