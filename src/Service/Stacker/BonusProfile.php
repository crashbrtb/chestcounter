<?php
declare(strict_types=1);

namespace App\Service\Stacker;

use App\Model\Entity\Troop;
use App\Model\Table\TroopsTable;

/**
 * The player's researched bonuses, and the rules for applying them to a unit.
 *
 * Every value is a percentage: 420 means +420%. A unit's effective stat is its
 * card value multiplied by (1 + total/100).
 *
 * Bonuses stack additively from three sources:
 *  - the unit's class (guardsman / specialist / monster / engineer), or the
 *    Epic Monster Hunter bonus when the unit is one;
 *  - its combat category (melee / mounted / ranged / flying);
 *  - its unit type, for the four families covered by Monsters Boost research
 *    (beast / giant / dragon / elemental).
 *
 * Strength additionally takes the "vs Epic Monsters" bonus, which the game
 * reports as a single figure applying to every unit in the march.
 */
class BonusProfile
{
    /**
     * @param array<string, float> $healthByClass Percent per troop class.
     * @param array<string, float> $healthByCategory Percent per combat category.
     * @param array<string, float> $healthByType Percent per Monsters Boost type.
     * @param array<string, float> $strengthByClass Percent per troop class.
     * @param array<string, float> $strengthByCategory Percent per combat category.
     * @param array<string, float> $strengthByType Percent per Monsters Boost type.
     * @param float $strengthVsEpicMonsters Percent applied to every unit.
     * @param bool $monsterBonusIncludesLowestType Whether the monster class
     *   bonus already contains the lowest of the four Monsters Boost research
     *   values. This mirrors how the game's army screen reports the figure: the
     *   class total for monsters is quoted with the weakest type folded in, so
     *   only the difference may be added again per type. Set it to false when
     *   the class figures were entered with no type research included.
     */
    public function __construct(
        private readonly array $healthByClass = [],
        private readonly array $healthByCategory = [],
        private readonly array $healthByType = [],
        private readonly array $strengthByClass = [],
        private readonly array $strengthByCategory = [],
        private readonly array $strengthByType = [],
        private readonly float $strengthVsEpicMonsters = 0.0,
        private readonly bool $monsterBonusIncludesLowestType = true,
    ) {
    }

    /**
     * Build a profile from a flat form payload.
     *
     * @param array<string, mixed> $data Raw request data.
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $section = static function (array $source, string $key): array {
            $values = $source[$key] ?? [];

            return is_array($values)
                ? array_map(static fn ($v): float => (float)$v, $values)
                : [];
        };

        $health = is_array($data['health'] ?? null) ? $data['health'] : [];
        $strength = is_array($data['strength'] ?? null) ? $data['strength'] : [];

        return new self(
            healthByClass: $section($health, 'class'),
            healthByCategory: $section($health, 'category'),
            healthByType: $section($health, 'type'),
            strengthByClass: $section($strength, 'class'),
            strengthByCategory: $section($strength, 'category'),
            strengthByType: $section($strength, 'type'),
            strengthVsEpicMonsters: (float)($data['strength_vs_epic_monsters'] ?? 0),
            monsterBonusIncludesLowestType: (bool)($data['monster_bonus_includes_lowest_type'] ?? true),
        );
    }

    /**
     * Effective health of a single soldier.
     *
     * @param \App\Model\Entity\Troop $troop Unit to measure.
     * @return float
     */
    public function effectiveHealth(Troop $troop): float
    {
        $total = $this->classBonus($troop, $this->healthByClass)
            + $this->categoryBonus($troop, $this->healthByCategory)
            + $this->typeBonus($troop, $this->healthByType);

        return (float)$troop->health * (1 + $total / 100);
    }

    /**
     * Effective strength of a single soldier, before feature bonuses against a
     * specific target.
     *
     * Engineer corps units keep their card strength: they are sacrificial and
     * are deliberately left out of strike-order balancing.
     *
     * @param \App\Model\Entity\Troop $troop Unit to measure.
     * @return float
     */
    public function effectiveStrength(Troop $troop): float
    {
        if ($troop->troop_class === 'engineer') {
            return (float)$troop->strength;
        }

        $total = $this->classBonus($troop, $this->strengthByClass)
            + $this->categoryBonus($troop, $this->strengthByCategory)
            + $this->typeBonus($troop, $this->strengthByType)
            + $this->strengthVsEpicMonsters;

        return (float)$troop->strength * (1 + $total / 100);
    }

    /**
     * True when any strength bonus is set, so the caller can skip the
     * strike-order pass entirely when there is nothing to balance.
     *
     * @return bool
     */
    public function hasStrengthBonuses(): bool
    {
        if ($this->strengthVsEpicMonsters > 0) {
            return true;
        }

        foreach ([$this->strengthByClass, $this->strengthByCategory, $this->strengthByType] as $set) {
            foreach ($set as $value) {
                if ((float)$value !== 0.0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Class-level bonus, with Epic Monster Hunters taking their own figure.
     *
     * @param \App\Model\Entity\Troop $troop Unit to measure.
     * @param array<string, float> $set Bonus set to read from.
     * @return float
     */
    private function classBonus(Troop $troop, array $set): float
    {
        if ($troop->category === 'epic_monster_hunter') {
            return (float)($set['epic_monster_hunter'] ?? 0);
        }

        return (float)($set[$troop->troop_class] ?? 0);
    }

    /**
     * Category bonus. Siege, scout and Epic Monster Hunter units have no
     * category bonus of their own.
     *
     * @param \App\Model\Entity\Troop $troop Unit to measure.
     * @param array<string, float> $set Bonus set to read from.
     * @return float
     */
    private function categoryBonus(Troop $troop, array $set): float
    {
        return (float)($set[$troop->category] ?? 0);
    }

    /**
     * Monsters Boost bonus for the four boostable unit types.
     *
     * Monster-class units only receive the difference over the weakest of the
     * four researches when the class figure already folds that weakest value
     * in; other classes always receive the full per-type figure.
     *
     * @param \App\Model\Entity\Troop $troop Unit to measure.
     * @param array<string, float> $set Bonus set to read from.
     * @return float
     */
    private function typeBonus(Troop $troop, array $set): float
    {
        if (!in_array($troop->unit_type, TroopsTable::BOOSTABLE_TYPES, true)) {
            return 0.0;
        }

        $mine = (float)($set[$troop->unit_type] ?? 0);

        if ($troop->troop_class !== 'monster' || !$this->monsterBonusIncludesLowestType) {
            return $mine;
        }

        $lowest = null;
        foreach (TroopsTable::BOOSTABLE_TYPES as $type) {
            $value = (float)($set[$type] ?? 0);
            $lowest = $lowest === null ? $value : min($lowest, $value);
        }

        return max(0.0, $mine - (float)$lowest);
    }
}
