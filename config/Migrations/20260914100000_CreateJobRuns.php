<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * A heartbeat for the jobs the site depends on.
 *
 * Watching the newest chest said whether chests arrived, not whether the
 * collector ran: a quiet day raised the same alarm as a dead collector, and the
 * backup and the maintenance were not watched at all. Every job now writes one
 * row per run, and `/api/v1/health` compares the last good run of each job with
 * how long it may stay silent.
 *
 * - `job_runs`         one row per run: which job, from where, how it ended
 *                      and a JSON summary. The collector writes it straight to
 *                      MySQL, the way it writes chests.
 * - `monitored_jobs`   the jobs the health check expects, and for how long each
 *                      may go without a good run before it counts as down.
 * - config `health_check_key`  the read-only key the external monitor sends.
 */
class CreateJobRuns extends AbstractMigration
{
    /**
     * @var array<string, string>
     */
    private const TABLE_OPTIONS = [
        'encoding' => 'utf8mb4',
        'collation' => 'utf8mb4_general_ci',
    ];

    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        if (!$this->hasTable('job_runs')) {
            $this->table('job_runs', self::TABLE_OPTIONS)
                ->addColumn('job', 'string', ['limit' => 40, 'null' => false])
                ->addColumn('status', 'string', [
                    'limit' => 16,
                    'null' => false,
                    'comment' => 'running | success | partial | failed | cancelled',
                ])
                ->addColumn('host', 'string', ['limit' => 100, 'null' => true, 'default' => null])
                ->addColumn('started_at', 'datetime', ['null' => false])
                ->addColumn('finished_at', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('summary', 'text', ['null' => true, 'default' => null])
                ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
                ->addIndex(['job', 'started_at'], ['name' => 'job_runs_job_started'])
                ->create();
        }

        if (!$this->hasTable('monitored_jobs')) {
            $this->table('monitored_jobs', self::TABLE_OPTIONS)
                ->addColumn('job', 'string', ['limit' => 40, 'null' => false])
                ->addColumn('label', 'string', ['limit' => 80, 'null' => false])
                ->addColumn('max_silence_minutes', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'comment' => 'How long the job may go without a good run',
                ])
                ->addColumn('enabled', 'boolean', ['null' => false, 'default' => true])
                ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
                ->addIndex(['job'], ['unique' => true, 'name' => 'monitored_jobs_job_unique'])
                ->create();
        }

        $now = date('Y-m-d H:i:s');
        $jobs = [
            // Three hours; set it to about twice the collector's schedule under Admin > Monitoring.
            ['collector', 'Chest collector', 180, 1],
            // Twice a day by default (05:15 and 17:15 UTC): one missed run plus margin.
            ['daily_maintenance', 'Daily maintenance', 780, 1],
            ['database_backup', 'Database backup', 1500, 1],
            // Sent by hand from the EventUploader, so it is off until someone wants the reminder.
            ['tournament_import', 'Tournament upload', 1560, 0],
        ];
        foreach ($jobs as [$job, $label, $minutes, $enabled]) {
            if ($this->fetchRow(sprintf("SELECT id FROM monitored_jobs WHERE job = '%s'", $job))) {
                continue;
            }
            $this->table('monitored_jobs')->insert([
                'job' => $job,
                'label' => $label,
                'max_silence_minutes' => $minutes,
                'enabled' => $enabled,
                'created' => $now,
                'modified' => $now,
            ])->saveData();
        }

        if (!$this->fetchRow("SELECT id FROM config WHERE param = 'health_check_key'")) {
            $this->execute(sprintf(
                "INSERT INTO config (param, value, description) VALUES ('health_check_key', '%s', '%s')",
                'ccm_' . bin2hex(random_bytes(20)),
                'Read-only key the external monitor sends to /api/v1/health. Managed under Admin > Monitoring.'
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
        $this->execute("DELETE FROM config WHERE param = 'health_check_key'");

        foreach (['monitored_jobs', 'job_runs'] as $table) {
            if ($this->hasTable($table)) {
                $this->table($table)->drop()->save();
            }
        }
    }
}
