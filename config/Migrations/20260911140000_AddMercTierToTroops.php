<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add the mercenary band to the troops catalogue.
 *
 * Mercenaries are offered in bands -- 5, 6, 7 and 9 -- and only one band is for
 * hire at a time, following the player's best guardsmen. The band a mercenary
 * belongs to could not be read off its level alone, so it gets its own column.
 */
class AddMercTierToTroops extends AbstractMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        $table = $this->table('troops');

        if (!$table->hasColumn('merc_tier')) {
            $table
                ->addColumn('merc_tier', 'integer', [
                    'limit' => 4,
                    'null' => true,
                    'default' => null,
                    'after' => 'guardsmen_tiers',
                    'comment' => 'Which mercenary band a mercenary belongs to: 5, 6, 7 or 9',
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
        $table = $this->table('troops');

        if ($table->hasColumn('merc_tier')) {
            $table->removeColumn('merc_tier')->update();
        }
    }
}
