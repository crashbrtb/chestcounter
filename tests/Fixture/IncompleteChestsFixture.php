<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * IncompleteChestsFixture
 */
class IncompleteChestsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'name' => 'Pending Chest 1',
                'player' => 'Player One',
                'source' => 'Source One',
                'type' => 0,
                'status' => 'pending',
                'collected_chest_id' => null,
                'review_notes' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'collected_at' => '2026-09-08 05:48:19',
            ],
            [
                'id' => 2,
                'name' => 'Pending Chest 2',
                'player' => 'Player Two',
                'source' => 'Source Two',
                'type' => 0,
                'status' => 'pending',
                'collected_chest_id' => null,
                'review_notes' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'collected_at' => '2026-09-08 06:48:19',
            ],
            [
                'id' => 3,
                'name' => 'Corrected Chest',
                'player' => 'Player Three',
                'source' => 'Source Three',
                'type' => 0,
                'status' => 'corrected',
                'collected_chest_id' => 1,
                'review_notes' => null,
                'reviewed_by' => 1,
                'reviewed_at' => '2026-09-08 07:00:00',
                'collected_at' => '2026-09-08 04:48:19',
            ],
        ];
        parent::init();
    }
}
