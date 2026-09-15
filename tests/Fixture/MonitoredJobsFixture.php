<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * The jobs the CreateJobRuns migration seeds.
 */
class MonitoredJobsFixture extends TestFixture
{
    /**
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            ['id' => 1, 'job' => 'collector', 'label' => 'Chest collector', 'max_silence_minutes' => 180, 'enabled' => true],
            ['id' => 2, 'job' => 'daily_maintenance', 'label' => 'Daily maintenance', 'max_silence_minutes' => 780, 'enabled' => true],
            ['id' => 3, 'job' => 'database_backup', 'label' => 'Database backup', 'max_silence_minutes' => 1500, 'enabled' => true],
            ['id' => 4, 'job' => 'tournament_import', 'label' => 'Tournament upload', 'max_silence_minutes' => 1560, 'enabled' => false],
        ];
        parent::init();
    }
}
