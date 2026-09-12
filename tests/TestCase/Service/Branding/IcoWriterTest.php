<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Branding;

use App\Service\Branding\IcoWriter;
use Cake\TestSuite\TestCase;
use GdImage;
use InvalidArgumentException;

/**
 * App\Service\Branding\IcoWriter Test Case
 *
 * This class writes a binary container by hand, so the tests read the bytes
 * back rather than trusting that the file opens. Anything that only checked
 * "GD can still see an icon here" would pass on a file with a broken directory.
 *
 * @uses \App\Service\Branding\IcoWriter
 */
class IcoWriterTest extends TestCase
{
    /**
     * The header says what it should, and every entry points at real data.
     *
     * @return void
     */
    public function testDirectoryDescribesEveryEntry(): void
    {
        $ico = IcoWriter::fromImage($this->image(64), [16, 32, 48]);

        $header = unpack('vreserved/vtype/vcount', substr($ico, 0, 6));
        $this->assertSame(0, $header['reserved']);
        $this->assertSame(1, $header['type'], 'Type 1 is an icon; 2 would be a cursor.');
        $this->assertSame(3, $header['count']);

        $expected = [16, 32, 48];
        foreach ($expected as $i => $side) {
            $entry = unpack(
                'Cwidth/Cheight/Ccolours/Creserved/vplanes/vbits/Vbytes/Voffset',
                substr($ico, 6 + 16 * $i, 16)
            );

            $this->assertSame($side, $entry['width']);
            $this->assertSame($side, $entry['height']);
            $this->assertSame(0, $entry['reserved']);
            $this->assertSame(32, $entry['bits']);

            // The offset and length have to land inside the file and describe
            // an image of the size the entry claims.
            $payload = substr($ico, $entry['offset'], $entry['bytes']);
            $this->assertSame($entry['bytes'], strlen($payload));

            $info = getimagesizefromstring($payload);
            $this->assertSame(IMAGETYPE_PNG, $info[2]);
            $this->assertSame($side, $info[0]);
            $this->assertSame($side, $info[1]);
        }
    }

    /**
     * The result is something the rest of the world recognises as an icon.
     *
     * @return void
     */
    public function testResultIsRecognisedAsAnIcon(): void
    {
        $info = getimagesizefromstring(IcoWriter::fromImage($this->image(64), [16, 32]));

        $this->assertSame(IMAGETYPE_ICO, $info[2]);
    }

    /**
     * Sizes are de-duplicated and ordered, so a careless caller cannot produce
     * a file with two entries claiming the same dimensions.
     *
     * @return void
     */
    public function testSizesAreDeduplicatedAndSorted(): void
    {
        $ico = IcoWriter::fromImage($this->image(64), [32, 16, 32]);

        $header = unpack('vreserved/vtype/vcount', substr($ico, 0, 6));
        $this->assertSame(2, $header['count']);

        $first = unpack('Cwidth', substr($ico, 6, 1));
        $this->assertSame(16, $first['width']);
    }

    /**
     * A caller that asks for nothing usable is told, rather than being handed
     * an icon with no images in it.
     *
     * @return void
     */
    public function testEmptySizeListIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        IcoWriter::fromImage($this->image(64), [0, -8, 900]);
    }

    /**
     * The dimensions of an uploaded icon can be read back without decoding it.
     *
     * @return void
     */
    public function testLargestSizeIsReadBack(): void
    {
        $ico = IcoWriter::fromImage($this->image(64), [16, 48, 32]);

        $this->assertSame([48, 48], IcoWriter::readLargestSize($ico));
    }

    /**
     * A file that is not an icon is reported as such rather than guessed at.
     *
     * @param string $bytes What was uploaded.
     * @return void
     * @dataProvider notAnIconProvider
     */
    public function testNonIconsAreRejected(string $bytes): void
    {
        $this->assertNull(IcoWriter::readLargestSize($bytes));
    }

    /**
     * Things an .ico upload might turn out to be.
     *
     * @return array<string, array{string}>
     */
    public static function notAnIconProvider(): array
    {
        return [
            'empty' => [''],
            'too short to hold a directory' => ["\x00\x00\x01\x00\x01\x00"],
            'a cursor, not an icon' => [pack('vvv', 0, 2, 1) . str_repeat("\x00", 16)],
            'no entries' => [pack('vvv', 0, 1, 0) . str_repeat("\x00", 16)],
            'plain text' => ['this is not an icon at all, it is a sentence'],
        ];
    }

    /**
     * A square canvas to encode.
     *
     * @param int $side Size in pixels.
     * @return \GdImage
     */
    protected function image(int $side): GdImage
    {
        $image = imagecreatetruecolor($side, $side);
        imagefilledrectangle($image, 0, 0, $side, $side, (int)imagecolorallocate($image, 200, 40, 40));

        return $image;
    }
}
