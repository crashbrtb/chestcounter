<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateBankTables extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        // Tabela bank_accounts
        $this->table('bank_accounts')
            ->addColumn('member_id', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('balance', 'decimal', [
                'precision' => 15,
                'scale' => 2,
                'default' => '0.00',
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addForeignKey('member_id', 'members', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
            ->addIndex('member_id', ['unique' => true])
            ->create();

        // Tabela bank_transactions
        $this->table('bank_transactions')
            ->addColumn('member_id', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('type', 'string', [
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('amount', 'decimal', [
                'precision' => 15,
                'scale' => 2,
                'null' => false,
            ])
            ->addColumn('fee', 'decimal', [
                'precision' => 15,
                'scale' => 2,
                'null' => false,
            ])
            ->addColumn('final_amount', 'decimal', [
                'precision' => 15,
                'scale' => 2,
                'null' => false,
            ])
            ->addColumn('description', 'string', [
                'limit' => 512,
                'null' => true,
            ])
            ->addColumn('status', 'string', [
                'limit' => 20,
                'default' => 'pending',
                'null' => false,
            ])
            ->addColumn('destination_member_id', 'integer', [
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addForeignKey('member_id', 'members', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
            ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
            ->addForeignKey('destination_member_id', 'members', 'id', ['delete'=> 'SET_NULL', 'update'=> 'CASCADE'])
            ->create();

        // Tabela bank_approval_logs
        $this->table('bank_approval_logs')
            ->addColumn('bank_transaction_id', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('admin_user_id', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('action', 'string', [
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('original_values', 'json', [
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addForeignKey('bank_transaction_id', 'bank_transactions', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
            ->addForeignKey('admin_user_id', 'users', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
            ->create();
    }
}
