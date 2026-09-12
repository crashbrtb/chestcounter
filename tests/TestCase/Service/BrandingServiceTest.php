<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\Branding\BrandingImageException;
use App\Service\Branding\IcoWriter;
use App\Service\BrandingService;
use Cake\Core\Configure;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;
use GdImage;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;

/**
 * App\Service\BrandingService Test Case
 *
 * Every test runs against a throwaway directory rather than the real webroot:
 * these tests write files, and a test run must never be able to replace the
 * logo of the site it is running next to.
 *
 * @uses \App\Service\BrandingService
 */
class BrandingServiceTest extends TestCase
{
    use LocatorAwareTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Config',
    ];

    /**
     * @var \App\Service\BrandingService
     */
    protected BrandingService $service;

    /**
     * The disposable branding root for one test.
     *
     * @var string
     */
    protected string $root;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->root = TMP . 'branding-test-' . uniqid() . DS;
        mkdir($this->root, 0777, true);
        Configure::write('Branding.root', $this->root);

        $this->service = new BrandingService();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        Configure::delete('Branding.root');
        $this->removeTree($this->root);

        unset($this->service);

        parent::tearDown();
    }

    /**
     * A stored choice is used when the artwork it names is on disk.
     *
     * @return void
     */
    public function testSelectedPresetIsUsedWhenItsFilesExist(): void
    {
        $this->givenPreset('war-axe');
        $this->service->selectLogo('war-axe');

        $this->assertSame('war-axe', $this->service->logoSlug());
        $this->assertStringContainsString('/img/branding/presets/war-axe/logo.png', $this->service->logoUrl());
    }

    /**
     * A choice whose files have gone falls back rather than serving a 404 on
     * every page of the site.
     *
     * @return void
     */
    public function testChoiceFallsBackWhenArtworkIsMissing(): void
    {
        $this->givenPreset('war-axe');
        $this->service->selectLogo('war-axe');

        $this->removeTree(BrandingService::directoryFor('war-axe'));

        $this->assertSame(BrandingService::DEFAULT_SLUG, $this->service->logoSlug());
    }

    /**
     * An unknown slug is refused before anything is written.
     *
     * @return void
     */
    public function testSelectingAnUnknownSlugIsRefused(): void
    {
        $this->expectException(BrandingImageException::class);

        $this->service->selectLogo('not-a-preset');
    }

    /**
     * Choosing a preset that exists in the manifest but has never been drawn is
     * refused too: the manifest is not evidence that the file is there.
     *
     * @return void
     */
    public function testSelectingAPresetWithNoFilesIsRefused(): void
    {
        $this->expectException(BrandingImageException::class);

        $this->service->selectLogo('war-axe');
    }

    /**
     * The choice is written to the `config` table, creating the row on an
     * install that predates the migration.
     *
     * @return void
     */
    public function testChoiceIsStoredInTheConfigTable(): void
    {
        $this->givenPreset('ogre-skull');
        $this->service->selectLogo('ogre-skull');

        $row = $this->fetchTable('Config')
            ->find()
            ->where(['param' => BrandingService::PARAM_LOGO])
            ->first();

        $this->assertNotNull($row);
        $this->assertSame('ogre-skull', $row->value);
    }

    /**
     * A good logo is re-encoded as a PNG and bounded on its longest side.
     *
     * @return void
     */
    public function testUploadedLogoIsStoredAsABoundedPng(): void
    {
        $this->service->storeCustomLogo($this->upload($this->png(900, 450), 'logo.png'));

        $path = BrandingService::directoryFor(BrandingService::CUSTOM) . 'logo.png';
        $this->assertFileExists($path);

        $info = getimagesize($path);
        $this->assertSame('image/png', $info['mime']);
        $this->assertSame(BrandingService::LOGO_STORED_SIDE, $info[0]);
        // The aspect ratio of the original survives the resize.
        $this->assertSame(BrandingService::LOGO_STORED_SIDE / 2, $info[1]);
        $this->assertTrue($this->service->hasCustom('logo'));
    }

    /**
     * A JPEG logo is accepted and comes out as a PNG like every other upload.
     *
     * @return void
     */
    public function testUploadedJpegLogoIsConvertedToPng(): void
    {
        $this->service->storeCustomLogo($this->upload($this->jpeg(600, 600), 'logo.jpg'));

        $path = BrandingService::directoryFor(BrandingService::CUSTOM) . 'logo.png';
        $info = getimagesize($path);
        $this->assertSame('image/png', $info['mime']);
    }

    /**
     * Something that is not an image at all is refused, whatever it is named.
     *
     * @return void
     */
    public function testNonImageUploadIsRefused(): void
    {
        $this->expectException(BrandingImageException::class);

        $this->service->storeCustomLogo($this->upload('this is not an image', 'logo.png'));
    }

    /**
     * The dimension rules are enforced in both directions.
     *
     * @param int $width Uploaded width.
     * @param int $height Uploaded height.
     * @return void
     * @dataProvider badLogoDimensionsProvider
     */
    public function testLogoDimensionsAreEnforced(int $width, int $height): void
    {
        $this->expectException(BrandingImageException::class);

        $this->service->storeCustomLogo($this->upload($this->png($width, $height), 'logo.png'));
    }

    /**
     * Sizes and shapes a logo may not be.
     *
     * @return array<string, array{int, int}>
     */
    public static function badLogoDimensionsProvider(): array
    {
        return [
            'below the minimum' => [64, 64],
            'above the maximum' => [2400, 2400],
            'taller than it is wide' => [200, 400],
            'wider than the ratio allows' => [1600, 200],
        ];
    }

    /**
     * A logo exactly on each boundary is accepted: the rules stated on the
     * form are inclusive, and they have to behave that way.
     *
     * @return void
     */
    public function testLogoBoundariesAreInclusive(): void
    {
        $this->service->storeCustomLogo($this->upload(
            $this->png(BrandingService::LOGO_MIN_SIDE, BrandingService::LOGO_MIN_SIDE),
            'logo.png'
        ));
        $this->assertTrue($this->service->hasCustom('logo'));

        // Three times wider than it is tall, which is the stated maximum.
        $this->service->storeCustomLogo($this->upload($this->png(1200, 400), 'logo.png'));
        $this->assertTrue($this->service->hasCustom('logo'));
    }

    /**
     * A favicon upload produces both the PNG and a real .ico.
     *
     * @return void
     */
    public function testUploadedFaviconProducesPngAndIco(): void
    {
        $this->service->storeCustomFavicon($this->upload($this->png(128, 128), 'icon.png'));

        $directory = BrandingService::directoryFor(BrandingService::CUSTOM);
        $this->assertFileExists($directory . 'favicon.png');
        $this->assertFileExists($directory . 'favicon.ico');

        $png = getimagesize($directory . 'favicon.png');
        $this->assertSame(BrandingService::FAVICON_STORED_SIDE, $png[0]);
        $this->assertSame(BrandingService::FAVICON_STORED_SIDE, $png[1]);

        $ico = getimagesize($directory . 'favicon.ico');
        $this->assertSame(IMAGETYPE_ICO, $ico[2]);
    }

    /**
     * A favicon has to be square, because it is always drawn in a square.
     *
     * @return void
     */
    public function testNonSquareFaviconIsRefused(): void
    {
        $this->expectException(BrandingImageException::class);

        $this->service->storeCustomFavicon($this->upload($this->png(128, 64), 'icon.png'));
    }

    /**
     * A few pixels out of square is a rounding accident, not a different
     * intention, so it is squared up instead of rejected.
     *
     * @return void
     */
    public function testNearlySquareFaviconIsAccepted(): void
    {
        $this->service->storeCustomFavicon($this->upload($this->png(128, 122), 'icon.png'));

        $this->assertTrue($this->service->hasCustom('favicon'));
    }

    /**
     * An .ico upload is kept as it is: GD cannot open one to do better, and it
     * already holds whatever sizes its author chose.
     *
     * @return void
     */
    public function testUploadedIcoIsStoredVerbatim(): void
    {
        $source = imagecreatetruecolor(64, 64);
        $bytes = IcoWriter::fromImage($source, [16, 32, 64]);
        imagedestroy($source);

        $this->service->storeCustomFavicon($this->upload($bytes, 'favicon.ico'));

        $directory = BrandingService::directoryFor(BrandingService::CUSTOM);
        $this->assertSame($bytes, file_get_contents($directory . 'favicon.ico'));
        // No PNG is written, because there is nothing to write one from.
        $this->assertFileDoesNotExist($directory . 'favicon.png');
    }

    /**
     * Replacing a raster favicon with an .ico clears the stale PNG, which the
     * layout would otherwise keep pointing the browser at.
     *
     * @return void
     */
    public function testIcoUploadClearsAPreviousPng(): void
    {
        $this->service->storeCustomFavicon($this->upload($this->png(128, 128), 'icon.png'));
        $directory = BrandingService::directoryFor(BrandingService::CUSTOM);
        $this->assertFileExists($directory . 'favicon.png');

        $source = imagecreatetruecolor(64, 64);
        $ico = IcoWriter::fromImage($source, [32]);
        imagedestroy($source);
        $this->service->storeCustomFavicon($this->upload($ico, 'favicon.ico'));

        $this->assertFileDoesNotExist($directory . 'favicon.png');
    }

    /**
     * An upload past the ceiling is refused on its size alone.
     *
     * @return void
     */
    public function testOversizedFaviconIsRefused(): void
    {
        $this->expectException(BrandingImageException::class);

        $this->service->storeCustomFavicon(new UploadedFile(
            $this->stream($this->png(64, 64)),
            BrandingService::FAVICON_MAX_BYTES + 1,
            UPLOAD_ERR_OK,
            'icon.png',
            'image/png'
        ));
    }

    /**
     * A failed upload is reported rather than being read as an empty file.
     *
     * @return void
     */
    public function testFailedUploadIsRefused(): void
    {
        $this->expectException(BrandingImageException::class);

        $this->service->storeCustomLogo(new UploadedFile(
            $this->stream(''),
            0,
            UPLOAD_ERR_PARTIAL,
            'logo.png',
            'image/png'
        ));
    }

    /**
     * Choosing a favicon refreshes the copy that browsers request by name.
     *
     * @return void
     */
    public function testSelectingAFaviconPublishesTheRootCopy(): void
    {
        $this->givenPreset('tower-shield');
        $this->service->selectFavicon('tower-shield');

        $published = rtrim($this->root, DS) . DS . 'favicon.ico';
        $this->assertFileExists($published);
        $this->assertSame(
            file_get_contents(BrandingService::directoryFor('tower-shield') . 'favicon.ico'),
            file_get_contents($published)
        );
    }

    /**
     * Both link tags are offered when both files exist, so a browser can take
     * the PNG and anything older can still find the .ico.
     *
     * @return void
     */
    public function testFaviconLinksCoverBothFormats(): void
    {
        $this->givenPreset('castle-keep');
        $this->service->selectFavicon('castle-keep');

        $types = array_column($this->service->faviconLinks(), 'type');

        $this->assertContains('image/x-icon', $types);
        $this->assertContains('image/png', $types);
    }

    /**
     * The URL carries a stamp, so replacing a custom logo in place is not
     * hidden behind the browser cache.
     *
     * @return void
     */
    public function testLogoUrlIsCacheBusted(): void
    {
        $this->givenPreset('crown-of-war');
        $this->service->selectLogo('crown-of-war');

        $this->assertMatchesRegularExpression('/\?v=\d+$/', $this->service->logoUrl());
    }

    /**
     * Every preset the picker offers can actually be drawn.
     *
     * @return void
     */
    public function testEveryOfferedPresetHasArtworkInWebroot(): void
    {
        Configure::delete('Branding.root');

        foreach (array_keys(BrandingService::PRESETS) as $slug) {
            $directory = BrandingService::directoryFor($slug);
            foreach (['logo.png', 'favicon.png', 'favicon.ico'] as $file) {
                $this->assertFileExists($directory . $file, sprintf('%s is missing %s', $slug, $file));
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────

    /**
     * Put a preset's files where the service expects to find them.
     *
     * The bytes do not matter to anything under test here, only that a file of
     * that name exists, so a tiny PNG stands in for the real artwork.
     *
     * @param string $slug The preset to create.
     * @return void
     */
    protected function givenPreset(string $slug): void
    {
        $directory = BrandingService::directoryFor($slug);
        mkdir($directory, 0777, true);

        $image = imagecreatetruecolor(16, 16);
        file_put_contents($directory . 'logo.png', $this->encodePng($image));
        file_put_contents($directory . 'favicon.png', $this->encodePng($image));
        file_put_contents($directory . 'favicon.ico', IcoWriter::fromImage($image, [16]));
        imagedestroy($image);
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
        return new UploadedFile(
            $this->stream($bytes),
            strlen($bytes),
            UPLOAD_ERR_OK,
            $name,
            // Deliberately wrong: the service must decide the type by reading
            // the file, not by believing the browser.
            'application/octet-stream'
        );
    }

    /**
     * Wrap bytes in a stream.
     *
     * @param string $bytes The contents.
     * @return \Laminas\Diactoros\Stream
     */
    protected function stream(string $bytes): Stream
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write($bytes);
        $stream->rewind();

        return $stream;
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
        $bytes = $this->encodePng($image);
        imagedestroy($image);

        return $bytes;
    }

    /**
     * A JPEG of the given size.
     *
     * @param int $width Width in pixels.
     * @param int $height Height in pixels.
     * @return string
     */
    protected function jpeg(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        ob_start();
        imagejpeg($image);
        $bytes = (string)ob_get_clean();
        imagedestroy($image);

        return $bytes;
    }

    /**
     * Encode an image as PNG bytes.
     *
     * @param GdImage $image The image.
     * @return string
     */
    protected function encodePng(GdImage $image): string
    {
        ob_start();
        imagepng($image);

        return (string)ob_get_clean();
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
