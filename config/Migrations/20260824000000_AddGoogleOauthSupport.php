<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Migration: Add Google OAuth support and active status to users table.
 */
class AddGoogleOauthSupport extends AbstractMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        $table = $this->table('users');

        if (!$table->hasColumn('google_id')) {
            $table->addColumn('google_id', 'string', [
                'limit' => 255,
                'null' => true,
                'default' => null,
                'after' => 'email',
            ]);
        }

        if (!$table->hasColumn('active')) {
            $table->addColumn('active', 'boolean', [
                'null' => false,
                'default' => 1,
                'after' => 'google_id',
            ]);
        }

        if ($table->hasColumn('password')) {
            $table->changeColumn('password', 'string', [
                'limit' => 255,
                'null' => true,
                'default' => null,
            ]);
        }

        if (!$table->hasIndex(['google_id'])) {
            $table->addIndex(['google_id'], [
                'unique' => true,
                'name' => 'idx_users_google_id',
            ]);
        }

        $table->update();
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        $table = $this->table('users');

        if ($table->hasIndex(['google_id'])) {
            $table->removeIndexByName('idx_users_google_id');
        }

        if ($table->hasColumn('google_id')) {
            $table->removeColumn('google_id');
        }

        if ($table->hasColumn('active')) {
            $table->removeColumn('active');
        }

        if ($table->hasColumn('password')) {
            $table->changeColumn('password', 'string', [
                'limit' => 255,
                'null' => false,
            ]);
        }

        $table->update();
    }
}
