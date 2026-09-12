<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\Branding\IcoWriter;
use App\Service\Branding\PresetPainter;
use App\Service\BrandingService;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Writes the shipped logo and favicon artwork into webroot.
 *
 * The files this produces are committed, so a normal deployment never has to
 * run it. It exists for the two moments that do need it: changing one of the
 * emblems, and an install whose webroot was rebuilt from something other than a
 * checkout and came out missing the images.
 */
class BrandingPresetsCommand extends Command
{
    /**
     * Size of the logo written for each preset, in pixels.
     */
    private const LOGO_SIZE = 512;

    /**
     * Size of the favicon PNG written for each preset, in pixels.
     */
    private const FAVICON_SIZE = 256;

    /**
     * How much more of the medallion the emblem fills on the favicon.
     *
     * A favicon is usually drawn at 16 pixels, where the rim eats most of the
     * image; cropping in a little is what keeps the emblem legible there.
     */
    private const FAVICON_EMBLEM_SCALE = 1.18;

    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser->setDescription(
            'Draws the shipped branding presets into webroot/img/branding/presets. '
            . 'Existing files are left alone unless --force is given.'
        )
            ->addOption('force', [
                'boolean' => true,
                'help' => 'Redraw presets whose files are already on disk.',
            ])
            ->addOption('only', [
                'help' => 'Draw just one preset, by slug.',
            ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        if (!extension_loaded('gd')) {
            $io->error('This command needs the GD extension, which is not loaded.');

            return self::CODE_ERROR;
        }

        $missing = PresetPainter::missingArtwork();
        if ($missing !== []) {
            $io->error(sprintf(
                'These presets are offered but cannot be drawn: %s',
                implode(', ', $missing)
            ));

            return self::CODE_ERROR;
        }

        $only = $args->getOption('only');
        $slugs = PresetPainter::slugs();
        if ($only !== null) {
            if (!in_array($only, $slugs, true)) {
                $io->error(sprintf('There is no preset called "%s".', $only));
                $io->info('Known presets: ' . implode(', ', $slugs));

                return self::CODE_ERROR;
            }
            $slugs = [$only];
        }

        $force = (bool)$args->getOption('force');
        $painter = new PresetPainter();
        $written = 0;
        $skipped = 0;

        foreach ($slugs as $slug) {
            $directory = BrandingService::directoryFor($slug);

            if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
                $io->error(sprintf('Could not create %s.', $directory));

                return self::CODE_ERROR;
            }

            if (!$force && is_file($directory . 'logo.png') && is_file($directory . 'favicon.ico')) {
                $io->verbose(sprintf('%s: already drawn, skipping.', $slug));
                $skipped++;
                continue;
            }

            $logo = $painter->paint($slug, self::LOGO_SIZE);
            imagepng($logo, $directory . 'logo.png', 9);
            imagedestroy($logo);

            $icon = $painter->paint($slug, self::FAVICON_SIZE, self::FAVICON_EMBLEM_SCALE);
            imagepng($icon, $directory . 'favicon.png', 9);
            file_put_contents($directory . 'favicon.ico', IcoWriter::fromImage($icon));
            imagedestroy($icon);

            $io->out(sprintf('<success>drawn</success> %s', $slug));
            $written++;
        }

        $io->out(sprintf('%d preset(s) drawn, %d already present.', $written, $skipped));

        return self::CODE_SUCCESS;
    }
}
