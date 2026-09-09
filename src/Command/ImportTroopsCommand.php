<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * ImportTroops command.
 *
 * Loads the unit catalogue used by the stack calculator from a JSON file,
 * matching rows on their slug so the command can be re-run after a game patch
 * without losing the units a player has disabled.
 */
class ImportTroopsCommand extends Command
{
    use LocatorAwareTrait;

    /**
     * Dataset shipped with the application.
     */
    private const DEFAULT_FILE = CONFIG . 'data' . DS . 'troops.json';

    /**
     * Hook method for defining this command's option parser.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser->setDescription('Imports the troop catalogue used by the stack calculator.')
            ->addArgument('file', [
                'help' => 'JSON file to import. Defaults to config/data/troops.json.',
                'required' => false,
            ])
            ->addOption('prune', [
                'boolean' => true,
                'help' => 'Delete catalogue rows that are absent from the file.',
            ]);

        return $parser;
    }

    /**
     * Import the catalogue.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int The exit code.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $file = (string)($args->getArgument('file') ?? self::DEFAULT_FILE);

        if (!is_readable($file)) {
            $io->error(sprintf('Cannot read %s', $file));

            return self::CODE_ERROR;
        }

        $payload = json_decode((string)file_get_contents($file), true);
        if (!is_array($payload) || !is_array($payload['troops'] ?? null)) {
            $io->error('The file does not contain a "troops" list.');

            return self::CODE_ERROR;
        }

        $table = $this->fetchTable('Troops');
        $existing = $table->find()->all()->indexBy('slug')->toArray();

        $created = 0;
        $updated = 0;
        $failed = 0;
        $seen = [];

        foreach ($payload['troops'] as $row) {
            if (!is_array($row) || !isset($row['slug'])) {
                $failed++;
                continue;
            }

            $slug = (string)$row['slug'];
            $seen[$slug] = true;
            $isNew = !isset($existing[$slug]);
            $entity = $existing[$slug] ?? $table->newEmptyEntity();

            // `enabled` is a player setting, not catalogue data: never let an
            // import switch a unit back on behind their back.
            unset($row['enabled']);

            $table->patchEntity($entity, $row);

            if ($table->save($entity)) {
                $isNew ? $created++ : $updated++;
                continue;
            }

            $failed++;
            $io->warning(sprintf(
                '%s: %s',
                $slug,
                json_encode($entity->getErrors(), JSON_UNESCAPED_UNICODE) ?: 'unknown error',
            ));
        }

        $pruned = 0;
        if ($args->getOption('prune')) {
            foreach ($existing as $slug => $entity) {
                if (!isset($seen[$slug]) && $table->delete($entity)) {
                    $pruned++;
                }
            }
        }

        $io->success(sprintf(
            '%d created, %d updated, %d pruned, %d failed.',
            $created,
            $updated,
            $pruned,
            $failed,
        ));

        return $failed > 0 ? self::CODE_ERROR : self::CODE_SUCCESS;
    }
}
