<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add the nightly database backup settings to the config table.
 *
 * The backup replaces the `backup_database.sh` script that used to be edited on
 * the server, so the three things that were variables at the top of that script
 * become rows here: whether it runs, where it writes and how long dumps are
 * kept. The time it runs is not among them — it is pinned to the 17:00 UTC game
 * reset in \App\Service\DatabaseBackupService.
 *
 * `config.value` is widened on the way, because it held 45 characters and a
 * backup folder is a full filesystem path: MySQL would have silently truncated
 * it into a different folder.
 */
class AddDatabaseBackupConfig extends AbstractMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        $this->table('config')
            ->changeColumn('value', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->update();

        $rows = [
            [
                'param' => 'database_backup_enabled',
                'value' => '0',
                'description' => 'Whether the nightly database backup runs. Managed under Admin > Maintenance.',
            ],
            [
                'param' => 'database_backup_dir',
                'value' => '~/bkpdb',
                'description' => 'Folder the nightly database dumps are written to. "~" is the home directory '
                    . 'of the user the site runs as. Managed under Admin > Maintenance.',
            ],
            [
                'param' => 'database_backup_retention_days',
                'value' => '7',
                'description' => 'How many days of database dumps to keep. Older dumps are deleted after each '
                    . 'backup. Managed under Admin > Maintenance.',
            ],
        ];

        foreach ($rows as $row) {
            $existing = $this->fetchRow(sprintf(
                "SELECT id FROM config WHERE param = '%s'",
                $row['param']
            ));

            if ($existing) {
                continue;
            }

            $this->execute(sprintf(
                "INSERT INTO config (param, value, description) VALUES ('%s', '%s', '%s')",
                $row['param'],
                $row['value'],
                str_replace("'", "''", $row['description'])
            ));
        }
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        $this->execute(
            'DELETE FROM config WHERE param IN '
                . "('database_backup_enabled', 'database_backup_dir', 'database_backup_retention_days')"
        );

        $this->table('config')
            ->changeColumn('value', 'string', [
                'limit' => 45,
                'null' => false,
            ])
            ->update();
    }
}
