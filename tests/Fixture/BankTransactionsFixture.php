<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * BankTransactionsFixture
 */
class BankTransactionsFixture extends TestFixture
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
                'member_id' => 1,
                'user_id' => 1,
                'type' => 'Lorem ipsum dolor ',
                'amount' => 1.5,
                'fee' => 1.5,
                'final_amount' => 1.5,
                'description' => 'Lorem ipsum dolor sit amet',
                'status' => 'Lorem ipsum dolor ',
                'destination_member_id' => 1,
                'created' => '2025-11-16 18:29:15',
                'modified' => '2025-11-16 18:29:15',
            ],
        ];
        parent::init();
    }
}
