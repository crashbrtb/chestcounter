<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MembersFixture
 */
class MembersFixture extends TestFixture
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
                'player' => 1,
                'active' => 1,
                'created_at' => 1740932342,
                'modified_at' => 1740932342,
            ],
        ];
        parent::init();
    }
}
