<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PlayerNameMappingsFixture
 */
class PlayerNameMappingsFixture extends TestFixture
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
                'ocr_text' => 'Lorem ipsum dolor sit amet',
                'correct_name' => 'Lorem ipsum dolor sit amet',
                'created' => 1763314445,
                'modified' => 1763314445,
            ],
        ];
        parent::init();
    }
}
