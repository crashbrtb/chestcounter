<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\FrozenTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use DateTime;

/**
 * DailyMaintenance command.
 *
 * Orchestrates daily automated maintenance tasks:
 * 1. Process pending/unprocessed completed cycle summaries.
 * 2. Purge old collected chests based on retention configuration.
 */
class DailyMaintenanceCommand extends Command
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

        $parser->setDescription('Runs daily automated maintenance tasks (cycle summaries processing and old data purge).')
            ->addOption('dry-run', [
                'boolean' => true,
                'help' => 'Perform a dry run without modifying the database.',
            ])
            ->addOption('skip-summaries', [
                'boolean' => true,
                'help' => 'Skip cycle summaries processing step.',
            ])
            ->addOption('skip-purge', [
                'boolean' => true,
                'help' => 'Skip old chests purge step.',
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
        $io->out('<info>Starting Daily Maintenance Task...</info>');
        $isDryRun = (bool)$args->getOption('dry-run');

        if ($isDryRun) {
            $io->warning('Running in DRY-RUN mode. No changes will be saved to database.');
        }

        // 1. Process Cycle Summaries
        if (!$args->getOption('skip-summaries')) {
            $io->hr();
            $io->out('<info>[Task 1/2] Processing pending cycle summaries...</info>');
            $this->runProcessCycleSummaries($io, $isDryRun);
        } else {
            $io->out('[Task 1/2] Skipped cycle summaries processing (--skip-summaries specified).');
        }

        // 2. Purge Old Collected Chests
        if (!$args->getOption('skip-purge')) {
            $io->hr();
            $io->out('<info>[Task 2/2] Purging old collected chests...</info>');
            $this->runPurgeCollectedChests($io, $isDryRun);
        } else {
            $io->out('[Task 2/2] Skipped old chests purge (--skip-purge specified).');
        }

        $io->hr();
        $io->success('Daily Maintenance completed successfully.');

        return static::CODE_SUCCESS;
    }

    /**
     * Runs the process cycle summaries task for any completed cycle that has not been summarized yet.
     *
     * @param \Cake\Console\ConsoleIo $io
     * @param bool $isDryRun
     * @return void
     */
    protected function runProcessCycleSummaries(ConsoleIo $io, bool $isDryRun): void
    {
        $configsTable = $this->fetchTable('Config');
        $playerCycleSummariesTable = $this->fetchTable('PlayerCycleSummaries');

        $referenceDayConfig = $configsTable->find()->where(['param' => 'reference_day'])->first();
        $everyHowManyDaysConfig = $configsTable->find()->where(['param' => 'every_how_many_days'])->first();
        $minimumChestScoreConfig = $configsTable->find()->where(['param' => 'minimum_chest_score'])->first();

        if (!($referenceDayConfig && $everyHowManyDaysConfig && $minimumChestScoreConfig &&
              !empty($referenceDayConfig->value) && is_numeric($everyHowManyDaysConfig->value) && is_numeric($minimumChestScoreConfig->value))) {
            $io->error('Configuration parameters for cycle processing (reference_day, every_how_many_days, minimum_chest_score) are missing or invalid.');
            return;
        }

        $referenceDay = new FrozenTime($referenceDayConfig->value);
        $cycleDuration = (int)$everyHowManyDaysConfig->value;
        $minimumRequiredScore = (int)$minimumChestScoreConfig->value;

        $today = FrozenTime::now();
        $daysSinceReference = $referenceDay->diffInDays($today);
        $currentCycleOffset = (int)floor($daysSinceReference / $cycleDuration);

        // Check completed cycles starting from cycle 0 up to currentCycleOffset - 1
        $processedCountAll = 0;
        $unprocessedFound = 0;

        for ($offset = 0; $offset < $currentCycleOffset; $offset++) {
            $cycleStart = $referenceDay->addDays($offset * $cycleDuration);
            $cycleEnd = $cycleStart->addDays($cycleDuration)->sub(new \DateInterval('PT1S'));

            $existingSummaryCheck = $playerCycleSummariesTable->find()
                ->where(['cycle_start_date' => $cycleStart->format('Y-m-d')])
                ->count();

            if ($existingSummaryCheck > 0) {
                continue; // Cycle already processed
            }

            $unprocessedFound++;
            $io->out(sprintf('Found unprocessed cycle: %s to %s', $cycleStart->format('Y-m-d'), $cycleEnd->format('Y-m-d')));

            if ($isDryRun) {
                $io->out(' [DRY-RUN] Would process summaries for this cycle.');
                continue;
            }

            $result = $playerCycleSummariesTable->processCycleForDateRange(
                $cycleStart,
                $cycleEnd,
                $minimumRequiredScore,
                false
            );

            if ($result['processed'] > 0) {
                $io->success(sprintf(' Successfully processed %d player summaries for cycle %s.', $result['processed'], $cycleStart->format('Y-m-d')));
                $processedCountAll += $result['processed'];
            }
            if ($result['errors'] > 0) {
                $io->error(sprintf(' Encounted %d errors processing cycle %s.', $result['errors'], $cycleStart->format('Y-m-d')));
            }
        }

        if ($unprocessedFound === 0) {
            $io->out('No pending cycles to process. All completed cycles have summaries recorded.');
        }
    }

    /**
     * Runs the purge task for collected chests older than retention period.
     *
     * @param \Cake\Console\ConsoleIo $io
     * @param bool $isDryRun
     * @return void
     */
    protected function runPurgeCollectedChests(ConsoleIo $io, bool $isDryRun): void
    {
        $configTable = $this->fetchTable('Config');
        $collectedChestsTable = $this->fetchTable('CollectedChests');

        $config = $configTable->find()
            ->where(['param' => 'collected_chests_retention_days'])
            ->first();

        $retentionDays = $config ? (int)$config->value : 30;

        if ($retentionDays <= 0) {
            $io->out('Automatic purge is disabled in configuration (collected_chests_retention_days = 0).');
            return;
        }

        $cutoffDate = (new DateTime())
            ->modify("-{$retentionDays} days")
            ->format('Y-m-d H:i:s');

        $io->out(sprintf('Retention days: %d days (cutoff date: %s)', $retentionDays, $cutoffDate));

        $chestsToPurgeCount = $collectedChestsTable->find()
            ->where(['collected_at <' => $cutoffDate])
            ->count();

        if ($chestsToPurgeCount === 0) {
            $io->out('No old collected chests found to purge.');
            return;
        }

        if ($isDryRun) {
            $io->out(sprintf(' [DRY-RUN] Would purge %d old chest record(s).', $chestsToPurgeCount));
            return;
        }

        $count = $collectedChestsTable->deleteAll(['collected_at <' => $cutoffDate]);
        $io->success(sprintf('Successfully purged %d old collected chest record(s).', $count));
    }
}
