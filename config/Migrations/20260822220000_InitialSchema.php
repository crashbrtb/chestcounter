<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Initial database schema migration.
 *
 * Creates all 15 application tables with their indexes,
 * unique constraints, and foreign keys.
 *
 * Replaces the manual SQL dump (config/databasemodel.sql).
 */
class InitialSchema extends AbstractMigration
{
    /**
     * Up - Create all tables.
     *
     * Tables are created in dependency order:
     * 1. Independent tables first (no foreign keys)
     * 2. Tables with foreign keys after their dependencies
     */
    public function up(): void
    {
        // ── users ──────────────────────────────────────────────
        $this->table('users')
            ->addColumn('name', 'string', [
                'limit' => 60,
                'null' => false,
            ])
            ->addColumn('email', 'string', [
                'limit' => 60,
                'null' => false,
            ])
            ->addColumn('password', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->addColumn('modified', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->create();

        // ── roles ──────────────────────────────────────────────
        $this->table('roles')
            ->addColumn('name', 'string', [
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('description', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('alias', 'string', [
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->addColumn('modified', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->create();

        // ── roles_users ────────────────────────────────────────
        $this->table('roles_users')
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'default' => null,
                'signed' => true,
            ])
            ->addColumn('role_id', 'integer', [
                'null' => true,
                'default' => null,
                'signed' => true,
            ])
            ->addIndex(['user_id'], ['name' => 'fk_roles_users_users'])
            ->addIndex(['role_id'], ['name' => 'fk_roles_users_roles'])
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'NO_ACTION',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_roles_users_users',
            ])
            ->addForeignKey('role_id', 'roles', 'id', [
                'delete' => 'NO_ACTION',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_roles_users_roles',
            ])
            ->create();

        // ── members ────────────────────────────────────────────
        $this->table('members')
            ->addColumn('player', 'string', [
                'limit' => 45,
                'null' => false,
            ])
            ->addColumn('active', 'tinyinteger', [
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('created_at', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addColumn('modified_at', 'timestamp', [
                'null' => false,
            ])
            ->addColumn('power', 'integer', [
                'null' => false,
                'default' => 0,
                'signed' => true,
            ])
            ->addColumn('guards', 'integer', [
                'null' => false,
                'default' => 0,
                'signed' => true,
            ])
            ->addColumn('specialists', 'integer', [
                'null' => false,
                'default' => 0,
                'signed' => true,
            ])
            ->addColumn('monsters', 'integer', [
                'null' => false,
                'default' => 0,
                'signed' => true,
            ])
            ->addColumn('engineers', 'integer', [
                'null' => false,
                'default' => 0,
                'signed' => true,
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'default' => null,
                'signed' => true,
            ])
            ->addIndex(['user_id'], ['name' => 'fk_members_users'])
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
                'constraint' => 'fk_members_users',
            ])
            ->create();

        // ── config ─────────────────────────────────────────────
        $this->table('config')
            ->addColumn('param', 'string', [
                'limit' => 45,
                'null' => false,
            ])
            ->addColumn('value', 'string', [
                'limit' => 45,
                'null' => false,
            ])
            ->addColumn('description', 'string', [
                'limit' => 512,
                'null' => false,
            ])
            ->create();

        // ── standard_chests ────────────────────────────────────
        $this->table('standard_chests')
            ->addColumn('source', 'char', [
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('score', 'integer', [
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('monster', 'integer', [
                'null' => false,
                'default' => 0,
                'signed' => true,
                'comment' => '1 = Epic Monsters chest 0 = Regular chest',
            ])
            ->addColumn('qty_chest', 'integer', [
                'null' => true,
                'default' => null,
                'signed' => true,
                'comment' => 'If the chest type is epic monsters, inform the amount of chests earned by killing a monster',
            ])
            ->create();

        // ── collected_chests ───────────────────────────────────
        $this->table('collected_chests')
            ->addColumn('name', 'string', [
                'limit' => 50,
                'null' => false,
                'default' => '0',
            ])
            ->addColumn('player', 'string', [
                'limit' => 50,
                'null' => false,
                'default' => '0',
            ])
            ->addColumn('source', 'string', [
                'limit' => 50,
                'null' => false,
                'default' => '0',
            ])
            ->addColumn('type', 'tinyinteger', [
                'null' => false,
                'default' => 0,
                'signed' => true,
                'comment' => '0 = auto / 1 = Manual',
            ])
            ->addColumn('collected_at', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->create();

        // ── errors ─────────────────────────────────────────────
        $this->table('errors')
            ->addColumn('error_value', 'text', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('collected_at', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->create();

        // ── events ─────────────────────────────────────────────
        $this->table('events')
            ->addColumn('start_date', 'timestamp', [
                'null' => false,
            ])
            ->addColumn('end_date', 'timestamp', [
                'null' => false,
            ])
            ->create();

        // ── incomplete_chests ──────────────────────────────────
        $this->table('incomplete_chests')
            ->addColumn('name', 'string', [
                'limit' => 50,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('player', 'string', [
                'limit' => 50,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('source', 'string', [
                'limit' => 50,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('type', 'tinyinteger', [
                'null' => true,
                'default' => 0,
                'signed' => true,
                'comment' => '0 = auto / 1 = Manual',
            ])
            ->addColumn('collected_at', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->create();

        // ── player_cycle_summaries ─────────────────────────────
        $this->table('player_cycle_summaries')
            ->addColumn('player_name', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('cycle_start_date', 'date', [
                'null' => false,
            ])
            ->addColumn('cycle_end_date', 'date', [
                'null' => false,
            ])
            ->addColumn('total_chests', 'integer', [
                'null' => false,
                'default' => 0,
                'signed' => true,
            ])
            ->addColumn('total_score', 'integer', [
                'null' => false,
                'default' => 0,
                'signed' => true,
            ])
            ->addColumn('epic_crypt_score', 'integer', [
                'null' => false,
                'default' => 0,
                'signed' => true,
            ])
            ->addColumn('goal_achieved', 'boolean', [
                'null' => false,
                'default' => false,
            ])
            ->addColumn('fine_due', 'boolean', [
                'null' => false,
                'default' => false,
            ])
            ->addColumn('fine_paid', 'boolean', [
                'null' => false,
                'default' => false,
            ])
            ->addColumn('created', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addColumn('modified', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addIndex(['player_name', 'cycle_start_date'], [
                'unique' => true,
                'name' => 'UNIQUE_PLAYER_CYCLE',
            ])
            ->create();

        // ── player_name_mappings ───────────────────────────────
        $this->table('player_name_mappings')
            ->addColumn('ocr_text', 'string', [
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('correct_name', 'string', [
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addColumn('modified', 'timestamp', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addIndex(['ocr_text'], [
                'unique' => true,
                'name' => 'ocr_text',
            ])
            ->create();

        // ── bank_accounts ──────────────────────────────────────
        $this->table('bank_accounts')
            ->addColumn('member_id', 'integer', [
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('balance', 'decimal', [
                'precision' => 15,
                'scale' => 2,
                'null' => false,
                'default' => '0.00',
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->addColumn('modified', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->addIndex(['member_id'], [
                'unique' => true,
                'name' => 'member_id',
            ])
            ->addForeignKey('member_id', 'members', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'constraint' => 'bank_accounts_ibfk_1',
            ])
            ->create();

        // ── bank_transactions ──────────────────────────────────
        $this->table('bank_transactions')
            ->addColumn('member_id', 'integer', [
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'signed' => true,
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
                'default' => null,
            ])
            ->addColumn('status', 'string', [
                'limit' => 20,
                'null' => false,
                'default' => 'pending',
            ])
            ->addColumn('destination_member_id', 'integer', [
                'null' => true,
                'default' => null,
                'signed' => true,
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->addColumn('modified', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->addIndex(['member_id'], ['name' => 'member_id'])
            ->addIndex(['user_id'], ['name' => 'user_id'])
            ->addIndex(['destination_member_id'], ['name' => 'destination_member_id'])
            ->addForeignKey('member_id', 'members', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'constraint' => 'bank_transactions_ibfk_1',
            ])
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'constraint' => 'bank_transactions_ibfk_2',
            ])
            ->addForeignKey('destination_member_id', 'members', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
                'constraint' => 'bank_transactions_ibfk_3',
            ])
            ->create();

        // ── bank_approval_logs ─────────────────────────────────
        $this->table('bank_approval_logs')
            ->addColumn('bank_transaction_id', 'integer', [
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('admin_user_id', 'integer', [
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('action', 'string', [
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('original_values', 'text', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->addIndex(['bank_transaction_id'], ['name' => 'bank_transaction_id'])
            ->addIndex(['admin_user_id'], ['name' => 'admin_user_id'])
            ->addForeignKey('bank_transaction_id', 'bank_transactions', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'constraint' => 'bank_approval_logs_ibfk_1',
            ])
            ->addForeignKey('admin_user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'constraint' => 'bank_approval_logs_ibfk_2',
            ])
            ->create();
    }

    /**
     * Down - Drop all tables in reverse dependency order.
     */
    public function down(): void
    {
        $this->table('bank_approval_logs')->drop()->save();
        $this->table('bank_transactions')->drop()->save();
        $this->table('bank_accounts')->drop()->save();
        $this->table('player_name_mappings')->drop()->save();
        $this->table('player_cycle_summaries')->drop()->save();
        $this->table('incomplete_chests')->drop()->save();
        $this->table('events')->drop()->save();
        $this->table('errors')->drop()->save();
        $this->table('collected_chests')->drop()->save();
        $this->table('standard_chests')->drop()->save();
        $this->table('config')->drop()->save();
        $this->table('members')->drop()->save();
        $this->table('roles_users')->drop()->save();
        $this->table('roles')->drop()->save();
        $this->table('users')->drop()->save();
    }
}
