<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddBankFeeParams extends BaseMigration
{
    public function up(): void
    {
        $defaults = [
            [
                'param' => 'deposit_fee',
                'value' => '0',
                'description' => 'Fixed deposit fee in millions of Silver',
            ],
            [
                'param' => 'withdrawal_fee',
                'value' => '0',
                'description' => 'Fixed withdrawal fee in millions of Silver',
            ],
            [
                'param' => 'transfer_fee',
                'value' => '0',
                'description' => 'Fixed transfer fee in millions of Silver',
            ],
            [
                'param' => 'caravan_fee',
                'value' => '0',
                'description' => 'Caravan fee percentage for deposits',
            ],
        ];

        $table = $this->table('config');
        $inserts = [];

        foreach ($defaults as $row) {
            $exists = $this->fetchRow(
                sprintf("SELECT id FROM config WHERE param = '%s'", addslashes($row['param']))
            );

            if (!$exists) {
                $inserts[] = $row;
            }
        }

        if (!empty($inserts)) {
            $table->insert($inserts)->save();
        }
    }

    public function down(): void
    {
        $this->execute("DELETE FROM config WHERE param IN ('deposit_fee','withdrawal_fee','transfer_fee','caravan_fee')");
    }
}
