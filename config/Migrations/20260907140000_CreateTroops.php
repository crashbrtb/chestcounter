<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Create the troops catalogue used by the stack calculator.
 *
 * One row per unit variant (Archer I .. Purifier II), holding the base stats
 * shown on the unit card plus the feature bonuses against each target family.
 * Values are game facts and are refreshed by `bin/cake import_troops`.
 */
class CreateTroops extends AbstractMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        if ($this->hasTable('troops')) {
            return;
        }

        $this->table('troops')
            ->addColumn('slug', 'string', [
                'limit' => 80,
                'null' => false,
                'comment' => 'Stable identifier derived from the unit name',
            ])
            ->addColumn('name', 'string', [
                'limit' => 80,
                'null' => false,
            ])
            ->addColumn('troop_class', 'string', [
                'limit' => 16,
                'null' => false,
                'comment' => 'guardsman | specialist | monster | engineer',
            ])
            ->addColumn('category', 'string', [
                'limit' => 24,
                'null' => false,
                'comment' => 'melee | mounted | ranged | flying | siege | scout | epic_monster_hunter',
            ])
            ->addColumn('unit_type', 'string', [
                'limit' => 16,
                'null' => false,
                'comment' => 'human | beast | dragon | giant | elemental | demon | undead | elves | cursed',
            ])
            ->addColumn('level', 'integer', [
                'limit' => 4,
                'null' => false,
            ])
            ->addColumn('strength', 'biginteger', ['null' => false, 'default' => 0])
            ->addColumn('health', 'biginteger', ['null' => false, 'default' => 0])
            ->addColumn('cost_pool', 'string', [
                'limit' => 16,
                'null' => false,
                'comment' => 'Which army limit this unit consumes: leadership | authority | dominance',
            ])
            ->addColumn('cost', 'integer', [
                'null' => false,
                'default' => 0,
                'comment' => 'Units of cost_pool consumed per soldier',
            ])
            ->addColumn('leadership', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('authority', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('dominance', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('speed', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('initiative', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('food_consumption', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('carrying_capacity', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('revival_gold', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('revival_silver', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('is_mercenary', 'boolean', ['null' => false, 'default' => false])
            ->addColumn('guardsmen_tiers', 'string', [
                'limit' => 40,
                'null' => true,
                'default' => null,
                'comment' => 'JSON list of guardsman tiers a mercenary can be hired at, e.g. [6,7,8,9]',
            ])
            ->addColumn('is_temporary', 'boolean', ['null' => false, 'default' => false])
            ->addColumn('double_damage_chance', 'decimal', [
                'precision' => 6,
                'scale' => 3,
                'null' => false,
                'default' => 0,
                'comment' => 'Percent, e.g. 10.500 for a 10.5% chance',
            ])
            ->addColumn('player_battle_multiplier', 'decimal', [
                'precision' => 6,
                'scale' => 3,
                'null' => false,
                'default' => 1,
            ])
            ->addColumn('bonuses', 'text', [
                'null' => true,
                'default' => null,
                'comment' => 'JSON map of feature bonuses in percent, e.g. {"vs_melee":176}',
            ])
            ->addColumn('enabled', 'boolean', [
                'null' => false,
                'default' => true,
                'comment' => 'Unchecked units are hidden from the calculator',
            ])
            ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
            ->addIndex(['slug'], ['unique' => true, 'name' => 'troops_slug_unique'])
            ->addIndex(['name'], ['unique' => true, 'name' => 'troops_name_unique'])
            ->addIndex(['troop_class', 'level'], ['name' => 'troops_class_level'])
            ->create();
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        if ($this->hasTable('troops')) {
            $this->table('troops')->drop()->save();
        }
    }
}
