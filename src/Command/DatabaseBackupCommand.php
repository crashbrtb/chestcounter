<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\DatabaseBackupService;
use App\Service\Maintenance\BackupException;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * DatabaseBackup command.
 *
 * The nightly `mysqldump` cron calls, and the same thing an administrator can
 * run by hand. It replaces the `backup_database.sh` script that used to live in
 * the project root: the credentials now come from the application's own database
 * connection, and the folder and retention period from Admin > Maintenance.
 *
 * Cron installs it at 17:00 UTC, the game's daily reset, so each dump holds a
 * whole cycle day exactly as the game closed it.
 */
class DatabaseBackupCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser->setDescription(
            'Dumps the application database to the configured backup folder, compresses it and '
                . 'deletes dumps older than the configured retention period.'
        )
            ->addOption('dry-run', [
                'boolean' => true,
                'help' => 'Report what would be written and deleted without touching anything.',
            ])
            ->addOption('force', [
                'boolean' => true,
                'help' => 'Take a backup even when the nightly backup is turned off in the configuration.',
            ]);

        return $parser;
    }

    /**
     * Take the backup.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code, or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $backup = new DatabaseBackupService();
        $isDryRun = (bool)$args->getOption('dry-run');

        if (!$backup->isEnabled() && !$args->getOption('force')) {
            // Cron keeps calling this until the entry is removed, so a disabled
            // backup has to be a quiet success rather than a nightly failure in
            // the log.
            $io->out('The nightly database backup is turned off in the configuration (Admin > Maintenance).');
            $io->out('Run it with --force to take one anyway.');

            return static::CODE_SUCCESS;
        }

        $io->out('<info>Starting database backup...</info>');
        if ($isDryRun) {
            $io->warning('Running in DRY-RUN mode. Nothing will be written or deleted.');
        }

        $io->out(sprintf('Folder: %s', $backup->resolvedDirectory()));
        $io->out(sprintf('Retention: %d day(s)', $backup->retentionDays()));
        $io->hr();

        try {
            $result = $backup->run(function (string $line) use ($io): void {
                $io->out($line);
            }, $isDryRun);
        } catch (BackupException $e) {
            $io->hr();
            $io->error($e->getMessage());

            return static::CODE_ERROR;
        }

        $io->hr();

        if ($isDryRun) {
            $io->success('Dry run finished. Nothing was written or deleted.');

            return static::CODE_SUCCESS;
        }

        $io->success(sprintf(
            'Backup written to %s (%s). %d dump(s) kept, %d removed.',
            (string)$result['file'],
            $backup->humanBytes($result['bytes']),
            $result['kept'],
            count($result['removed'])
        ));

        return static::CODE_SUCCESS;
    }
}
