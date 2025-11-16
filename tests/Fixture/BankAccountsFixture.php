<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * BankAccountsFixture
 */
class BankAccountsFixture extends TestFixture
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
                'balance' => 1.5,
                'created' => '2025-11-16 18:28:41',
                'modified' => '2025-11-16 18:28:41',
            ],
        ];
        parent::init();
    }
}
