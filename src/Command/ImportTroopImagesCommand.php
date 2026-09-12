<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Entity\Troop;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * ImportTroopImages command.
 *
 * Copies unit portraits into webroot/img/troops, named after the unit's slug so
 * the calculator can find them without a database lookup.
 *
 * Source files are matched by name rather than by path, because the captures
 * they come from label them in several ways: "Corax II.png", "G9_Corax II.png"
 * and "guardas_g9_corax_ii.png" all describe the same unit.
 */
class ImportTroopImagesCommand extends Command
{
    use LocatorAwareTrait;

    /**
     * Where the calculator looks for portraits.
     */
    public const TARGET_DIR = WWW_ROOT . 'img' . DS . 'troops';

    /**
     * Side of the square the portraits are fitted into.
     */
    private const DEFAULT_SIZE = 64;

    /**
     * Labels the capture tools prepend to a file name that are not part of the
     * unit's name.
     */
    private const NAME_PREFIXES = '/^(?:[gsme]\d+|guardas|especialistas|monstros'
        . '|engenheiros|mercenarios|merc)[_ ]/i';

    /**
     * Hook method for defining this command's option parser.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser->setDescription('Imports unit portraits for the troop calculator.')
            ->addArgument('sources', [
                'help' => 'One or more folders holding the image files, separated by commas.',
                'required' => true,
            ])
            ->addOption('size', [
                'short' => 's',
                'help' => 'Side of the square each portrait is fitted into. Defaults to 64.',
                'default' => (string)self::DEFAULT_SIZE,
            ])
            ->addOption('force', [
                'boolean' => true,
                'help' => 'Replace portraits that are already in place.',
            ]);

        return $parser;
    }

    /**
     * Import the portraits.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int The exit code.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        if (!extension_loaded('gd')) {
            $io->error('The gd extension is required to resize the portraits.');

            return self::CODE_ERROR;
        }

        $size = max(16, (int)$args->getOption('size'));
        $force = (bool)$args->getOption('force');

        $sources = array_filter(array_map('trim', explode(',', (string)$args->getArgument('sources'))));
        $files = $this->collectFiles($sources, $io);
        if ($files === []) {
            $io->error('No image files found in the given folders.');

            return self::CODE_ERROR;
        }

        if (!is_dir(self::TARGET_DIR) && !mkdir(self::TARGET_DIR, 0775, true) && !is_dir(self::TARGET_DIR)) {
            $io->error(sprintf('Cannot create %s', self::TARGET_DIR));

            return self::CODE_ERROR;
        }

        /** @var list<\App\Model\Entity\Troop> $troops */
        $troops = $this->fetchTable('Troops')->find()->all()->toList();

        $written = 0;
        $kept = 0;
        $failed = 0;

        foreach ($troops as $troop) {
            $key = $this->normalise($troop->name);
            if (!isset($files[$key])) {
                continue;
            }

            $target = self::TARGET_DIR . DS . $troop->slug . '.png';
            if (!$force && is_file($target)) {
                $kept++;
                continue;
            }

            if ($this->writePortrait($files[$key], $target, $size)) {
                $written++;
                continue;
            }

            $failed++;
            $io->warning(sprintf('%s: could not read %s', $troop->name, $files[$key]));
        }

        // Report against what is actually on disk, not just what this run
        // matched: portraits imported earlier from another folder still count.
        $missing = array_values(array_filter(
            $troops,
            static fn (Troop $troop): bool => !is_file(self::TARGET_DIR . DS . $troop->slug . '.png'),
        ));
        $covered = count($troops) - count($missing);

        $io->success(sprintf(
            '%d written, %d already in place, %d failed. %d of %d units have a portrait.',
            $written,
            $kept,
            $failed,
            $covered,
            count($troops),
        ));

        if ($missing !== []) {
            $io->out('');
            $io->info(sprintf('%d units still have no portrait:', count($missing)));
            foreach ($missing as $troop) {
                $io->out(sprintf('  %-28s %s', $troop->name, $troop->group_code));
            }
        }

        return $failed > 0 ? self::CODE_ERROR : self::CODE_SUCCESS;
    }

    /**
     * Index every usable image in the given folders by its normalised name.
     *
     * Later folders win, so the caller can list a rough source first and a
     * cleaner one after it.
     *
     * @param list<string> $sources Folders to scan.
     * @param \Cake\Console\ConsoleIo $io The console io.
     * @return array<string, string> Normalised unit name => absolute file path.
     */
    private function collectFiles(array $sources, ConsoleIo $io): array
    {
        $found = [];

        foreach ($sources as $source) {
            if (!is_dir($source)) {
                $io->warning(sprintf('Not a folder, skipping: %s', $source));
                continue;
            }

            $count = 0;
            foreach ((array)scandir($source) as $entry) {
                $path = $source . DS . $entry;
                if (!is_file($path)) {
                    continue;
                }

                $extension = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
                if (!in_array($extension, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                    continue;
                }

                $found[$this->normalise(pathinfo($entry, PATHINFO_FILENAME))] = $path;
                $count++;
            }

            $io->out(sprintf('%s: %d image(s)', $source, $count));
        }

        return $found;
    }

    /**
     * Reduce a name to letters and digits so the different spellings the
     * capture tools produce all land on the same key.
     *
     * @param string $name Unit or file name.
     * @return string
     */
    private function normalise(string $name): string
    {
        $name = (string)iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        $name = trim(str_replace('_', ' ', $name));

        // The prefixes nest, as in "guardas_g9_corax_ii".
        for ($pass = 0; $pass < 3; $pass++) {
            $name = trim((string)preg_replace(self::NAME_PREFIXES, '', $name));
        }

        return strtolower((string)preg_replace('/[^A-Za-z0-9]/', '', $name));
    }

    /**
     * Fit one image into a transparent square and save it as a PNG.
     *
     * The image is scaled to fit rather than cropped, so tall card art keeps
     * its whole subject instead of losing the unit's head.
     *
     * @param string $source Path to read.
     * @param string $target Path to write.
     * @param int $size Side of the square.
     * @return bool Whether the portrait was written.
     */
    private function writePortrait(string $source, string $target, int $size): bool
    {
        $data = (string)file_get_contents($source);
        // Check the header first so a stray non-image file in the source folder
        // is reported rather than raising a warning from inside gd.
        if (getimagesizefromstring($data) === false) {
            return false;
        }

        $image = imagecreatefromstring($data);
        if ($image === false) {
            return false;
        }

        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        if ($sourceWidth < 1 || $sourceHeight < 1) {
            imagedestroy($image);

            return false;
        }

        $scale = min($size / $sourceWidth, $size / $sourceHeight);
        $width = max(1, (int)round($sourceWidth * $scale));
        $height = max(1, (int)round($sourceHeight * $scale));

        $canvas = imagecreatetruecolor($size, $size);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagealphablending($canvas, true);

        imagecopyresampled(
            $canvas,
            $image,
            (int)(($size - $width) / 2),
            (int)(($size - $height) / 2),
            0,
            0,
            $width,
            $height,
            $sourceWidth,
            $sourceHeight,
        );

        $ok = imagepng($canvas, $target, 9);

        imagedestroy($canvas);
        imagedestroy($image);

        return $ok;
    }
}
