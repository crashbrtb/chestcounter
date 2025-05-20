<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RolesFixture
 */
class RolesFixture extends TestFixture
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
                'name' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet',
                'alias' => 'Lorem ipsum dolor ',
                'created' => '2025-05-18 21:32:41',
                'modified' => '2025-05-18 21:32:41',
            ],
        ];
        parent::init();
    }
}
