<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add alias column to standard_chests migration.
 *
 * The alias is an optional friendly name used by reports instead of the raw
 * chest source, since monster chest sources often have confusing names.
 */
class AddAliasToStandardChests extends AbstractMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        $table = $this->table('standard_chests');

        if (!$table->hasColumn('alias')) {
            $table
                ->addColumn('alias', 'string', [
                    'limit' => 50,
                    'null' => true,
                    'default' => null,
                    'after' => 'source',
                    'comment' => 'Optional friendly name shown in reports instead of source',
                ])
                ->update();
        }
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        $table = $this->table('standard_chests');

        if ($table->hasColumn('alias')) {
            $table->removeColumn('alias')->update();
        }
    }
}
