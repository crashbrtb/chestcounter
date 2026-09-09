<?php
declare(strict_types=1);

namespace App\Service\Stacker;

use App\Model\Entity\Troop;

/**
 * One stack in the planned march: how many of a unit to bring, and what that
 * stack is worth once the player's bonuses are applied.
 */
class StackLine
{
    /**
     * @param \App\Model\Entity\Troop $troop The unit.
     * @param int $rank Position in the kill order, 0 being the first to die.
     * @param int $count How many soldiers to bring.
     * @param float $unitHealth Effective health of one soldier.
     * @param float $unitStrength Effective strength of one soldier.
     * @param bool $isSacrifice Whether this stack opens a round and so will be
     *   lost before it gets to strike.
     */
    public function __construct(
        public readonly Troop $troop,
        public readonly int $rank,
        public readonly int $count,
        public readonly float $unitHealth,
        public readonly float $unitStrength,
        public readonly bool $isSacrifice = false,
    ) {
    }

    /**
     * Total effective health of the stack.
     *
     * @return float
     */
    public function stackHealth(): float
    {
        return $this->count * $this->unitHealth;
    }

    /**
     * Total effective strength of the stack.
     *
     * @return float
     */
    public function stackStrength(): float
    {
        return $this->count * $this->unitStrength;
    }

    /**
     * Army limit consumed by the stack.
     *
     * @return int
     */
    public function costUsed(): int
    {
        return $this->count * $this->troop->cost;
    }

    /**
     * Gold needed to revive the whole stack after an attack.
     *
     * @return int
     */
    public function revivalGold(): int
    {
        return $this->count * $this->troop->revival_gold;
    }

    /**
     * Return a copy of this line with a different soldier count.
     *
     * @param int $count New count.
     * @return self
     */
    public function withCount(int $count): self
    {
        return new self(
            $this->troop,
            $this->rank,
            max(0, $count),
            $this->unitHealth,
            $this->unitStrength,
            $this->isSacrifice,
        );
    }

    /**
     * Serialize for the view layer and the JSON endpoint.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'slug' => $this->troop->slug,
            'name' => $this->troop->name,
            'group' => $this->troop->group_code,
            'class' => $this->troop->troop_class,
            'category' => $this->troop->category,
            'unit_type' => $this->troop->unit_type,
            'level' => $this->troop->level,
            'rank' => $this->rank,
            'count' => $this->count,
            'is_sacrifice' => $this->isSacrifice,
            'unit_health' => $this->unitHealth,
            'unit_strength' => $this->unitStrength,
            'stack_health' => $this->stackHealth(),
            'stack_strength' => $this->stackStrength(),
            'cost_pool' => $this->troop->cost_pool,
            'cost_used' => $this->costUsed(),
            'revival_gold' => $this->revivalGold(),
        ];
    }
}
