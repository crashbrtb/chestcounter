<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Troop Entity
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $troop_class
 * @property string $category
 * @property string $unit_type
 * @property int $level
 * @property int $strength
 * @property int $health
 * @property string $cost_pool
 * @property int $cost
 * @property int $leadership
 * @property int $authority
 * @property int $dominance
 * @property int $speed
 * @property int $initiative
 * @property int $food_consumption
 * @property int $carrying_capacity
 * @property int $revival_gold
 * @property int $revival_silver
 * @property bool $is_mercenary
 * @property string|null $guardsmen_tiers
 * @property int|null $merc_tier
 * @property bool $is_temporary
 * @property float $double_damage_chance
 * @property float $player_battle_multiplier
 * @property string|null $bonuses
 * @property bool $enabled
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property array<string, float> $bonus_map
 * @property list<int> $guardsmen_tier_list
 * @property string $group_code
 */
class Troop extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'slug' => true,
        'name' => true,
        'troop_class' => true,
        'category' => true,
        'unit_type' => true,
        'level' => true,
        'strength' => true,
        'health' => true,
        'cost_pool' => true,
        'cost' => true,
        'leadership' => true,
        'authority' => true,
        'dominance' => true,
        'speed' => true,
        'initiative' => true,
        'food_consumption' => true,
        'carrying_capacity' => true,
        'revival_gold' => true,
        'revival_silver' => true,
        'is_mercenary' => true,
        'guardsmen_tiers' => true,
        'merc_tier' => true,
        'is_temporary' => true,
        'double_damage_chance' => true,
        'player_battle_multiplier' => true,
        'bonuses' => true,
        'enabled' => true,
    ];

    /**
     * Virtual fields exposed when the entity is serialized.
     *
     * @var list<string>
     */
    protected array $_virtual = ['bonus_map', 'guardsmen_tier_list', 'group_code'];

    /**
     * Guardsman tiers this mercenary can be hired at.
     *
     * An empty list means the unit is not gated by tier, which is the case for
     * everything that is not a mercenary.
     *
     * @return list<int>
     */
    protected function _getGuardsmenTierList(): array
    {
        $decoded = $this->decodeList($this->guardsmen_tiers);

        return array_values(array_map('intval', $decoded));
    }

    /**
     * Decode a field that holds a JSON list, tolerating a value that is already
     * decoded so an entity built straight from the catalogue file behaves the
     * same as one loaded from the database.
     *
     * @param mixed $raw Stored value.
     * @return array<array-key, mixed>
     */
    private function decodeList(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if ($raw === null || $raw === '') {
            return [];
        }

        $decoded = json_decode((string)$raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Whether this unit belongs to a given mercenary band.
     *
     * Mercenaries come in bands -- 5, 6, 7 and 9 -- and the band on offer
     * follows the player's best guardsmen. Only one band is available at a
     * time, so a unit from any other band is simply not for hire. Anything that
     * is not a mercenary is never gated this way.
     *
     * @param int|null $tier The band the player can hire from.
     * @return bool
     */
    public function isInMercTier(?int $tier): bool
    {
        if (!$this->is_mercenary) {
            return true;
        }

        return $tier !== null && $this->merc_tier === $tier;
    }

    /**
     * Short code the calculator uses to group units, e.g. G9, S8, M7, E9.
     *
     * Mercenaries are pooled under a single code because they are picked by
     * authority rather than by tier.
     *
     * @return string
     */
    protected function _getGroupCode(): string
    {
        if ($this->is_mercenary) {
            return 'MERC';
        }

        $prefix = match ($this->troop_class) {
            'guardsman' => 'G',
            'specialist' => 'S',
            'monster' => 'M',
            'engineer' => 'E',
            default => '?',
        };

        return $prefix . $this->level;
    }

    /**
     * Feature bonuses decoded into a map of target key => percent.
     *
     * @return array<string, float>
     */
    protected function _getBonusMap(): array
    {
        return array_map(
            static fn ($value): float => (float)$value,
            $this->decodeList($this->bonuses),
        );
    }
}
