<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TroopsFixture
 *
 * A slice of the real catalogue: one guardsman and one specialist line at
 * level 9, a siege engine, a monster and a mercenary, which between them cover
 * every cost pool and every branch of the bonus rules.
 */
class TroopsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            $this->row([
                'id' => 1,
                'slug' => 'purifier-ii',
                'name' => 'Purifier II',
                'troop_class' => 'guardsman',
                'category' => 'ranged',
                'level' => 9,
                'strength' => 5510,
                'health' => 16530,
                'cost' => 1,
                'leadership' => 1,
                'bonuses' => '{"vs_melee":1333,"vs_flying":1717}',
            ]),
            $this->row([
                'id' => 2,
                'slug' => 'corax-ii',
                'name' => 'Corax II',
                'troop_class' => 'guardsman',
                'category' => 'flying',
                'level' => 9,
                'strength' => 110200,
                'health' => 330600,
                'cost' => 20,
                'leadership' => 20,
                'revival_gold' => 80,
                'bonuses' => '{"vs_mounted":1999}',
            ]),
            $this->row([
                'id' => 3,
                'slug' => 'duelist-ii',
                'name' => 'Duelist II',
                'troop_class' => 'specialist',
                'category' => 'melee',
                'level' => 9,
                'strength' => 5510,
                'health' => 16530,
                'cost' => 1,
                'leadership' => 1,
                'bonuses' => '{"vs_mounted":500,"vs_beasts":1025}',
            ]),
            $this->row([
                'id' => 4,
                'slug' => 'royal-lion-ii',
                'name' => 'Royal Lion II',
                'troop_class' => 'specialist',
                'category' => 'flying',
                'unit_type' => 'beast',
                'level' => 9,
                'strength' => 110200,
                'health' => 330600,
                'cost' => 20,
                'leadership' => 20,
                'revival_gold' => 80,
                'bonuses' => '{"vs_mounted":1000}',
            ]),
            $this->row([
                'id' => 5,
                'slug' => 'josephine-ii',
                'name' => 'Josephine II',
                'troop_class' => 'engineer',
                'category' => 'siege',
                'level' => 9,
                'strength' => 27550,
                'health' => 1487700,
                'cost' => 10,
                'leadership' => 10,
                'revival_gold' => 80,
                'bonuses' => '{"vs_fortifications":6500}',
            ]),
            $this->row([
                'id' => 6,
                'slug' => 'kraken-ii',
                'name' => 'Kraken II',
                'troop_class' => 'monster',
                'category' => 'ranged',
                'unit_type' => 'elemental',
                'level' => 9,
                'strength' => 3145000,
                'health' => 9435000,
                'cost_pool' => 'dominance',
                'cost' => 106,
                'dominance' => 106,
                'revival_gold' => 1310,
                'bonuses' => '{"vs_flying":1281}',
            ]),
            $this->row([
                'id' => 7,
                'slug' => 'overlord',
                'name' => 'Overlord',
                'troop_class' => 'monster',
                'category' => 'melee',
                'unit_type' => 'giant',
                'level' => 9,
                'strength' => 1300000,
                'health' => 3300000,
                'cost_pool' => 'authority',
                'cost' => 1000,
                'authority' => 1000,
                'is_mercenary' => true,
                'revival_gold' => 848,
                'bonuses' => '{"vs_melee":500}',
            ]),
            $this->row([
                'id' => 8,
                'slug' => 'trailseeker-vii',
                'name' => 'Trailseeker VII',
                'troop_class' => 'specialist',
                'category' => 'scout',
                'level' => 7,
                'strength' => 1700,
                'health' => 5100,
                'cost' => 1,
                'leadership' => 1,
                'bonuses' => '{}',
            ]),
        ];
        parent::init();
    }

    /**
     * Fill in the columns a row does not care about.
     *
     * @param array<string, mixed> $values Values that matter to the test.
     * @return array<string, mixed>
     */
    private function row(array $values): array
    {
        return $values + [
            'unit_type' => 'human',
            'cost_pool' => 'leadership',
            'leadership' => 0,
            'authority' => 0,
            'dominance' => 0,
            'speed' => 40,
            'initiative' => 10,
            'food_consumption' => 5,
            'carrying_capacity' => 100,
            'revival_gold' => 4,
            'revival_silver' => 40,
            'is_mercenary' => false,
            'is_temporary' => false,
            'double_damage_chance' => 0,
            'player_battle_multiplier' => 1,
            'bonuses' => '{}',
            'enabled' => true,
            'created' => '2026-09-07 12:00:00',
            'modified' => '2026-09-07 12:00:00',
        ];
    }
}
