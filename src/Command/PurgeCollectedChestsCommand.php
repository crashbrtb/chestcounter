<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\Locator\LocatorAwareTrait;
use DateTime;

/**
 * PurgeCollectedChests command.
 *
 * Automatically purges old collected chests based on the retention days setting
 * in the config table (collected_chests_retention_days).
 */
class PurgeCollectedChestsCommand extends Command
{
    use LocatorAwareTrait;

    /**
     * Hook method for defining this command's option parser.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser->setDescription('Purges old collected chests older than configured retention days.')
            ->addOption('days', [
                'short' => 'd',
                'help' => 'Override retention days specified in configuration.',
            ]);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null|void The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $configTable = $this->fetchTable('Config');
        $collectedChestsTable = $this->fetchTable('CollectedChests');

        $daysOption = $args->getOption('days');
        if ($daysOption !== null) {
            $retentionDays = (int)$daysOption;
        } else {
            $config = $configTable->find()
                ->where(['param' => 'collected_chests_retention_days'])
                ->first();

            $retentionDays = $config ? (int)$config->value : 30;
        }

        if ($retentionDays <= 0) {
            $io->out('Automatic purge is disabled (retention days set to 0).');

            return static::CODE_SUCCESS;
        }

        if ($retentionDays <= 7 && $daysOption === null) {
            $io->warning(sprintf('Retention days (%d) is <= 7. Retention period must be greater than 7 days.', $retentionDays));
        }

        $cutoffDate = (new DateTime())
            ->modify("-{$retentionDays} days")
            ->format('Y-m-d H:i:s');

        $io->out(sprintf('Purging collected chests created before %s (%d days retention)...', $cutoffDate, $retentionDays));

        $count = $collectedChestsTable->deleteAll(['collected_at <' => $cutoffDate]);

        $io->success(sprintf('Successfully purged %d old collected chest record(s).', $count));

        return static::CODE_SUCCESS;
    }
}
