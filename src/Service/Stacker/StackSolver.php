<?php
declare(strict_types=1);

namespace App\Service\Stacker;

use App\Model\Entity\Troop;

/**
 * Works out how many of each unit to bring on a march.
 *
 * The game resolves a fight by destroying whole stacks one at a time, and the
 * stacks are lost in descending order of total health: the biggest stack goes
 * first. So the order the stacks die in is decided entirely by their total
 * health -- give each stack slightly less health than the one before it and
 * they are lost in exactly the order you chose. Put the weakest units at the
 * front and the strongest survive the most rounds, which is where the extra
 * damage comes from.
 *
 * That turns planning a march into an allocation problem rather than a battle
 * simulation. Pick a target health K for the first stack to die, give rank i a
 * target of K x kof(i) where kof descends slightly with every rank, and the
 * soldier count follows as floor(target / health per soldier). Raising K spends
 * more of the army limit, so the largest K that still fits the limit is found
 * by bisection.
 *
 * Two refinements matter:
 *
 *  - Stacks strike in order of strength but die in order of health. If strength
 *    does not descend alongside health, a stack can be destroyed before it ever
 *    strikes. So each rank's target is also capped to keep stack strength
 *    descending (see applyStrikeOrder()).
 *  - Rounding the count down drifts by up to one soldier's health, which for
 *    heavy units is large enough to re-invert the order. The plan is repaired
 *    afterwards and the freed army limit is handed back out (see settleCounts()).
 */
class StackSolver
{
    /**
     * Relative health gap between consecutive ranks. Small enough not to waste
     * army limit, large enough to survive integer rounding.
     */
    private const RANK_GAP = 0.0015;

    /**
     * Relative strength gap enforced between consecutive striking stacks.
     */
    private const STRIKE_GAP = 0.001;

    /**
     * Bisection steps used to find the target health. 100 exhausts double
     * precision for any realistic army limit.
     */
    private const BISECTION_STEPS = 100;

    /**
     * Sections in the order they are lost, and the army limit each one spends.
     *
     * @var array<string, string>
     */
    private const SECTIONS = [
        'army' => 'leadership',
        'monsters' => 'dominance',
        'mercenaries' => 'authority',
    ];

    /**
     * Which mercenary band a player is offered, by their best guardsman tier.
     *
     * Mercenaries come in four bands -- 5, 6, 7 and 9 -- and only one is for
     * hire at a time. The bands do not line up one-to-one with guardsman
     * levels: everything from tier 6 up is offered band 9, which is why a
     * tier 8 account sees the same roster as a tier 9 one.
     *
     * Tiers 6 to 9 are confirmed in game; the rest come from the catalogue's
     * own availability data, except tiers 1 and 2, which are inferred as the
     * only remaining band.
     *
     * @var array<int, int>
     */
    private const MERC_BAND_BY_GUARDSMEN = [
        1 => 5,
        2 => 5,
        3 => 6,
        4 => 6,
        5 => 7,
        6 => 9,
        7 => 9,
        8 => 9,
        9 => 9,
    ];

    /**
     * @param list<\App\Model\Entity\Troop> $troops The unit catalogue.
     */
    public function __construct(private readonly array $troops)
    {
    }

    /**
     * Plan a march.
     *
     * @param \App\Service\Stacker\StackRequest $request What to plan for.
     * @return \App\Service\Stacker\StackPlan
     */
    public function solve(StackRequest $request): StackPlan
    {
        $caps = [
            'army' => $request->leadershipCap,
            'monsters' => $request->dominanceCap,
            'mercenaries' => $request->authorityCap,
        ];

        $sections = [];
        $warnings = [];
        $globalRank = 0;
        $ceiling = null;
        $strengthCeiling = null;

        foreach (self::SECTIONS as $section => $pool) {
            $candidates = $this->candidatesFor($section, $pool, $request);
            if ($candidates === [] || $caps[$section] <= 0) {
                $sections[$section] = [];
                continue;
            }

            $lines = $this->planSection(
                $candidates,
                $caps[$section],
                $ceiling,
                $strengthCeiling,
                $globalRank,
                $request,
            );

            // A unit the army limit could not reach is not in the march at all,
            // so it must not occupy a death slot. Drop those and plan again, so
            // the ranks -- and with them the sacrifice slots -- line up with the
            // stacks that will actually be fielded.
            $kept = $this->withSoldiers($lines);
            if ($kept !== [] && count($kept) !== count($lines)) {
                $keptSlugs = array_flip(array_map(
                    static fn (StackLine $line): string => $line->troop->slug,
                    $kept,
                ));
                $narrowed = array_values(array_filter(
                    $candidates,
                    static fn (Troop $troop): bool => isset($keptSlugs[$troop->slug]),
                ));
                $lines = $this->withSoldiers(
                    $this->planSection($narrowed, $caps[$section], $ceiling, $strengthCeiling, $globalRank, $request),
                );
            } else {
                $lines = $kept;
            }

            $sections[$section] = $lines;
            $globalRank += count($lines);

            $lowest = $this->lowestStackHealth($lines);
            if ($lowest > 0.0) {
                // Keep the next section strictly below this one so the overall
                // kill order runs army, then monsters, then mercenaries.
                $ceiling = $lowest * $request->sectionGap;
            }

            // Carry the strike order across the section boundary too: the last
            // stack that strikes here is what the next section must stay under.
            $trailing = $this->trailingStrikeStrength($lines);
            if ($trailing > 0.0) {
                $strengthCeiling = $trailing;
            }

            $used = 0;
            foreach ($lines as $line) {
                $used += $line->costUsed();
            }
            $unspent = $caps[$section] - $used;
            if ($unspent > 0 && $used > 0 && $unspent / $caps[$section] > 0.02) {
                $warnings[] = sprintf(
                    'The %s section left %s of %s unspent: the section ceiling binds before the limit does.',
                    $section,
                    number_format($unspent),
                    number_format($caps[$section]),
                );
            }
        }

        foreach (self::SECTIONS as $section => $pool) {
            if ($caps[$section] > 0 && ($sections[$section] ?? []) === []) {
                $warnings[] = sprintf(
                    'No %s units are available for the %s limit. Check the selected levels and the excluded units.',
                    $section,
                    $pool,
                );
            }
        }

        return new StackPlan($sections, $caps, $warnings);
    }

    /**
     * Units eligible for a section, already in kill order.
     *
     * @param string $section Section name.
     * @param string $pool Army limit the section spends.
     * @param \App\Service\Stacker\StackRequest $request The request.
     * @return list<\App\Model\Entity\Troop>
     */
    private function candidatesFor(string $section, string $pool, StackRequest $request): array
    {
        $excluded = array_flip($request->excludedSlugs);
        // Only one mercenary band is for hire at a time, and which one follows
        // the player's best guardsmen, so planning around a unit from another
        // band is wasted.
        $tier = $this->mercTier($request);

        $keep = static function (Troop $troop) use ($section, $pool, $excluded, $tier): bool {
            if (isset($excluded[$troop->slug]) || !$troop->enabled || $troop->cost <= 0) {
                return false;
            }
            if ($troop->cost_pool !== $pool) {
                return false;
            }
            // Scouts carry loot; they never take part in the kill order.
            if ($troop->category === 'scout') {
                return false;
            }

            if ($section !== 'mercenaries') {
                return !$troop->is_mercenary;
            }

            return $troop->is_mercenary && $troop->isInMercTier($tier);
        };

        $eligible = array_filter($this->troops, $keep);

        return $section === 'mercenaries'
            ? $this->orderMercenaries($eligible, $request)
            : $this->orderByKillOrder($eligible, $request);
    }

    /**
     * Which mercenary band is on offer.
     *
     * The player may name the band outright; otherwise it follows their best
     * guardsmen, through the fixed mapping above.
     *
     * @param \App\Service\Stacker\StackRequest $request The request.
     * @return int|null Null when no band applies, which leaves mercenaries out.
     */
    private function mercTier(StackRequest $request): ?int
    {
        if ($request->mercTier !== null) {
            return $request->mercTier;
        }

        $guardsmen = $this->highestGuardsmenTier($request);
        if ($guardsmen === null) {
            return null;
        }

        $band = self::MERC_BAND_BY_GUARDSMEN[$guardsmen] ?? null;
        if ($band === null) {
            return null;
        }

        // A band the catalogue does not carry is no use to anyone.
        foreach ($this->troops as $troop) {
            if ($troop->is_mercenary && $troop->merc_tier === $band) {
                return $band;
            }
        }

        return null;
    }

    /**
     * The player's best guardsman tier, read from the requested kill order.
     *
     * @param \App\Service\Stacker\StackRequest $request The request.
     * @return int|null Null when no guardsmen are being brought.
     */
    private function highestGuardsmenTier(StackRequest $request): ?int
    {
        $highest = null;
        foreach ($request->killOrder as $group) {
            if (!is_string($group) || $group === '' || $group[0] !== 'G') {
                continue;
            }

            $level = (int)substr($group, 1);
            if ($level > 0) {
                $highest = $highest === null ? $level : max($highest, $level);
            }
        }

        return $highest;
    }

    /**
     * Order units by the requested group order, then by category within a
     * group. Groups the player did not ask for are dropped.
     *
     * @param iterable<\App\Model\Entity\Troop> $troops Eligible units.
     * @param \App\Service\Stacker\StackRequest $request The request.
     * @return list<\App\Model\Entity\Troop>
     */
    private function orderByKillOrder(iterable $troops, StackRequest $request): array
    {
        $groupRank = array_flip($request->killOrder);
        $categoryRank = array_flip($request->categoryOrder);

        $ordered = [];
        foreach ($troops as $troop) {
            $group = $troop->group_code;
            if (!isset($groupRank[$group])) {
                continue;
            }
            $ordered[] = $troop;
        }

        usort($ordered, static function (Troop $a, Troop $b) use ($groupRank, $categoryRank): int {
            $byGroup = $groupRank[$a->group_code] <=> $groupRank[$b->group_code];
            if ($byGroup !== 0) {
                return $byGroup;
            }

            // Siege engines lead their own group: they are the sacrifice.
            $siege = ($b->category === 'siege' ? 1 : 0) <=> ($a->category === 'siege' ? 1 : 0);
            if ($siege !== 0) {
                return $siege;
            }

            $byCategory = ($categoryRank[$a->category] ?? PHP_INT_MAX) <=> ($categoryRank[$b->category] ?? PHP_INT_MAX);
            if ($byCategory !== 0) {
                return $byCategory;
            }

            return strcmp($a->name, $b->name);
        });

        return $ordered;
    }

    /**
     * Mercenaries are not tiered, so they are simply ordered weakest first by
     * the health each one buys per point of authority.
     *
     * @param iterable<\App\Model\Entity\Troop> $troops Eligible units.
     * @param \App\Service\Stacker\StackRequest $request The request.
     * @return list<\App\Model\Entity\Troop>
     */
    private function orderMercenaries(iterable $troops, StackRequest $request): array
    {
        $ordered = is_array($troops) ? array_values($troops) : iterator_to_array($troops, false);

        usort($ordered, function (Troop $a, Troop $b) use ($request): int {
            $ratio = static fn (Troop $t): float => $t->cost > 0
                ? $request->bonuses->effectiveHealth($t) / $t->cost
                : 0.0;

            return $ratio($a) <=> $ratio($b) ?: strcmp($a->name, $b->name);
        });

        return $ordered;
    }

    /**
     * Plan one section: rank the units, cap the targets, then find the largest
     * target health the army limit can pay for.
     *
     * @param list<\App\Model\Entity\Troop> $candidates Units in kill order.
     * @param int $cap Army limit for this section.
     * @param float|null $ceiling Highest stack health this section may reach.
     * @param float|null $strengthCeiling Strength of the last stack that struck
     *   in the previous section, which this one must stay under.
     * @param int $globalRank Rank offset across earlier sections.
     * @param \App\Service\Stacker\StackRequest $request The request.
     * @return list<\App\Service\Stacker\StackLine>
     */
    private function planSection(
        array $candidates,
        int $cap,
        ?float $ceiling,
        ?float $strengthCeiling,
        int $globalRank,
        StackRequest $request,
    ): array {
        $slots = [];
        foreach ($candidates as $index => $troop) {
            $health = $request->bonuses->effectiveHealth($troop);
            if ($health <= 0.0) {
                continue;
            }

            $slots[] = [
                'troop' => $troop,
                'rank' => $index,
                'health' => $health,
                'strength' => $request->bonuses->effectiveStrength($troop),
                'sacrifice' => $request->enemyStackCount > 0
                    && (($globalRank + $index) % $request->enemyStackCount) === 0,
                // Target health for rank i is K x factor. The factor decays
                // geometrically so it stays positive however long the list is.
                'factor' => (1 - self::RANK_GAP) ** $index,
            ];
        }

        if ($slots === []) {
            return [];
        }

        $enforceStrikeOrder = $request->enforceStrikeOrder && $request->bonuses->hasStrengthBonuses();
        if ($enforceStrikeOrder) {
            $slots = $this->applyStrikeOrder($slots);
        }

        $target = $this->largestAffordableTarget(
            $slots,
            $cap,
            $ceiling,
            $enforceStrikeOrder ? $strengthCeiling : null,
        );

        $lines = [];
        foreach ($slots as $slot) {
            $count = (int)floor($target * $slot['factor'] / $slot['health']);
            $lines[] = new StackLine(
                $slot['troop'],
                $slot['rank'],
                max(0, $count),
                $slot['health'],
                $slot['strength'],
                $slot['sacrifice'],
            );
        }

        return $this->settleCounts($lines, $cap, $ceiling, $enforceStrikeOrder, $strengthCeiling);
    }

    /**
     * Strength of the last stack in a section that gets to strike, which is
     * what the following section has to stay under.
     *
     * @param list<\App\Service\Stacker\StackLine> $lines Section lines.
     * @return float Zero when nothing in the section strikes.
     */
    private function trailingStrikeStrength(array $lines): float
    {
        for ($index = count($lines) - 1; $index >= 0; $index--) {
            $line = $lines[$index];
            if (
                $line->count > 0
                && !$line->isSacrifice
                && $line->troop->troop_class !== 'engineer'
            ) {
                return $line->stackStrength();
            }
        }

        return 0.0;
    }

    /**
     * Tighten each rank's target so stack strength descends alongside stack
     * health, which is what guarantees a stack strikes before it is destroyed.
     *
     * Stack strength at rank i is K x factor(i) x strength(i) / health(i), so
     * the constraint is independent of K and can be resolved up front.
     *
     * Engineer corps units are skipped: their strength is deliberately low and
     * forcing the chain through them would crush every rank behind them. Stacks
     * that open a round are still capped, because they are alive during the
     * earlier rounds and would otherwise take a strike slot from a stack that is
     * about to be lost; they just do not become the anchor for later ranks.
     *
     * @param list<array<string, mixed>> $slots Ranked slots.
     * @return list<array<string, mixed>>
     */
    private function applyStrikeOrder(array $slots): array
    {
        $anchor = null;

        foreach ($slots as $index => $slot) {
            if ($index === 0) {
                if (!$slot['sacrifice'] && $slot['troop']->troop_class !== 'engineer') {
                    $anchor = $slots[0];
                }
                continue;
            }

            $previous = $slots[$index - 1];
            $cap = $previous['factor'] * (1 - self::RANK_GAP);

            $isEngineer = $slot['troop']->troop_class === 'engineer';
            if (!$isEngineer && $anchor !== null && $slot['strength'] > 0.0 && $anchor['strength'] > 0.0) {
                $anchorRatio = $anchor['factor'] * $anchor['strength'] / $anchor['health'];
                $strikeCap = $anchorRatio * (1 - self::STRIKE_GAP) * $slot['health'] / $slot['strength'];
                $cap = min($cap, $strikeCap);
            }

            if ($cap > 0.0 && $cap < $slots[$index]['factor']) {
                $slots[$index]['factor'] = $cap;
            }

            if (!$slots[$index]['sacrifice'] && !$isEngineer) {
                $anchor = $slots[$index];
            }
        }

        return $slots;
    }

    /**
     * Bisect for the largest first-stack health the army limit can pay for.
     *
     * @param list<array<string, mixed>> $slots Ranked slots.
     * @param int $cap Army limit for this section.
     * @param float|null $ceiling Highest stack health this section may reach.
     * @param float|null $strengthCeiling Strength the section's striking stacks
     *   must stay under, carried over from the previous section.
     * @return float
     */
    private function largestAffordableTarget(
        array $slots,
        int $cap,
        ?float $ceiling,
        ?float $strengthCeiling = null,
    ): float {
        $low = 0.0;
        $high = 0.0;
        foreach ($slots as $slot) {
            $cost = $slot['troop']->cost;
            if ($cost > 0) {
                // Spending the whole limit on this one unit is an upper bound
                // for any stack's health, since no factor exceeds 1.
                $high = max($high, $cap / $cost * $slot['health']);
            }
        }

        if ($ceiling !== null) {
            $high = min($high, $ceiling / max($slots[0]['factor'], 1e-9));
        }

        // Stack strength at rank i is target x factor(i) x strength(i) /
        // health(i), so the incoming strength ceiling caps the target directly.
        if ($strengthCeiling !== null && $strengthCeiling > 0.0) {
            $allowed = $strengthCeiling * (1 - self::STRIKE_GAP);
            foreach ($slots as $slot) {
                if ($slot['troop']->troop_class === 'engineer' || $slot['strength'] <= 0.0) {
                    continue;
                }
                $high = min($high, $allowed * $slot['health'] / ($slot['factor'] * $slot['strength']));
            }
        }

        if ($high <= 0.0) {
            return 0.0;
        }

        for ($step = 0; $step < self::BISECTION_STEPS; $step++) {
            $mid = ($low + $high) / 2;
            $used = 0.0;
            foreach ($slots as $slot) {
                $used += floor($mid * $slot['factor'] / $slot['health']) * $slot['troop']->cost;
                if ($used > $cap) {
                    break;
                }
            }

            if ($used <= $cap) {
                $low = $mid;
            } else {
                $high = $mid;
            }
        }

        return $low;
    }

    /**
     * Repair the rounding damage, then hand the freed army limit back out.
     *
     * Flooring the count loses up to one soldier's worth of health per stack,
     * which for heavy units is enough to lift a stack above the one that should
     * outlive it. Trimming fixes the order but leaves army limit on the table,
     * so a second pass walks back up from the last stack and tops each one up
     * as far as the stack ahead of it allows.
     *
     * @param list<\App\Service\Stacker\StackLine> $lines Lines in kill order.
     * @param int $cap Army limit for this section.
     * @param float|null $ceiling Highest stack health this section may reach.
     * @param bool $enforceStrikeOrder Whether topping a stack up must also keep
     *   stack strength descending, not just stack health.
     * @param float|null $strengthCeiling Strength carried over from the previous
     *   section, which this section's first striking stack must stay under.
     * @return list<\App\Service\Stacker\StackLine>
     */
    private function settleCounts(
        array $lines,
        int $cap,
        ?float $ceiling,
        bool $enforceStrikeOrder,
        ?float $strengthCeiling = null,
    ): array {
        $used = 0;
        foreach ($lines as $line) {
            $used += $line->costUsed();
        }

        // Pass one: force both orders to descend strictly, walking forwards so
        // every stack is measured against the already-corrected one ahead of
        // it. Trimming a stack for health lowers its strength as well, which is
        // why the two rules have to be settled together rather than one after
        // the other. A unit that ended up with no soldiers is simply not in the
        // march, so it neither caps the stacks behind it nor breaks the chain.
        $previousHealth = $ceiling;
        $anchorStrength = $enforceStrikeOrder ? $strengthCeiling : null;

        foreach ($lines as $index => $line) {
            if ($line->count === 0) {
                continue;
            }

            $allowed = $line->count;

            if ($previousHealth !== null) {
                $allowed = min($allowed, (int)floor(($previousHealth - 1) / $line->unitHealth));
            }

            $isEngineer = $line->troop->troop_class === 'engineer';
            if ($enforceStrikeOrder && !$isEngineer && $anchorStrength !== null && $line->unitStrength > 0.0) {
                $allowed = min(
                    $allowed,
                    (int)floor($anchorStrength * (1 - self::STRIKE_GAP) / $line->unitStrength),
                );
            }

            $allowed = max(0, $allowed);
            if ($allowed < $line->count) {
                $used -= ($line->count - $allowed) * $line->troop->cost;
                $lines[$index] = $line->withCount($allowed);
            }

            $settled = $lines[$index];
            if ($settled->count === 0) {
                continue;
            }

            $previousHealth = $settled->stackHealth();
            if (!$settled->isSacrifice && !$isEngineer) {
                $anchorStrength = $settled->stackStrength();
            }
        }

        // Pass two: spend what pass one gave back, starting from the stacks
        // that survive longest since those are the ones worth enlarging. A
        // stack may only grow into the gap between the stack ahead of it and
        // the one behind it, so the kill order is preserved either way.
        for ($index = count($lines) - 1; $index >= 0; $index--) {
            $line = $lines[$index];
            $cost = $line->troop->cost;
            if ($cost <= 0 || $used + $cost > $cap) {
                continue;
            }

            $upper = $this->neighbourHealth($lines, $index, -1) ?? $ceiling;
            $lower = $this->neighbourHealth($lines, $index, 1) ?? 0.0;

            $affordable = $line->count + intdiv($cap - $used, $cost);
            $bounded = $upper === null
                ? $affordable
                : min($affordable, (int)floor(($upper - 1) / $line->unitHealth));

            // Growing a stack raises its strength too, so it must not overtake
            // the stack that strikes before it.
            // Engineer corps stacks are exempt from the strike order, so they
            // take neither the in-section anchor nor the section ceiling.
            $anchor = null;
            if ($enforceStrikeOrder && $line->troop->troop_class !== 'engineer') {
                $anchor = $this->strikeAnchorStrength($lines, $index) ?? $strengthCeiling;
            }
            if ($anchor !== null && $line->unitStrength > 0.0) {
                $bounded = min(
                    $bounded,
                    (int)floor($anchor * (1 - self::STRIKE_GAP) / $line->unitStrength),
                );
            }

            if ($bounded <= $line->count || $bounded * $line->unitHealth <= $lower) {
                continue;
            }

            $used += ($bounded - $line->count) * $cost;
            $lines[$index] = $line->withCount($bounded);
        }

        return array_values($lines);
    }

    /**
     * Health of the nearest stack with soldiers in it, scanning in one
     * direction from a starting index.
     *
     * @param list<\App\Service\Stacker\StackLine> $lines Section lines.
     * @param int $from Index to start from, exclusive.
     * @param int $step -1 to look towards the front, 1 towards the back.
     * @return float|null Null when there is no such stack.
     */
    private function neighbourHealth(array $lines, int $from, int $step): ?float
    {
        for ($index = $from + $step; isset($lines[$index]); $index += $step) {
            if ($lines[$index]->count > 0) {
                return $lines[$index]->stackHealth();
            }
        }

        return null;
    }

    /**
     * Strength of the stack that strikes immediately before the one at $from.
     *
     * Engineer corps stacks are skipped because they are exempt from the
     * strike-order rule, and so are the stacks that open a round, since those
     * are lost before they can take a strike slot from the stack behind them.
     *
     * @param list<\App\Service\Stacker\StackLine> $lines Section lines.
     * @param int $from Index to look back from, exclusive.
     * @return float|null Null when nothing ahead of it strikes, or when the
     *   stack at $from is itself exempt.
     */
    private function strikeAnchorStrength(array $lines, int $from): ?float
    {
        if (!isset($lines[$from]) || $lines[$from]->troop->troop_class === 'engineer') {
            return null;
        }

        for ($index = $from - 1; $index >= 0; $index--) {
            $line = $lines[$index];
            if (
                $line->count > 0
                && !$line->isSacrifice
                && $line->troop->troop_class !== 'engineer'
            ) {
                return $line->stackStrength();
            }
        }

        return null;
    }

    /**
     * Keep only the stacks that ended up with soldiers in them.
     *
     * @param list<\App\Service\Stacker\StackLine> $lines Section lines.
     * @return list<\App\Service\Stacker\StackLine>
     */
    private function withSoldiers(array $lines): array
    {
        return array_values(array_filter(
            $lines,
            static fn (StackLine $line): bool => $line->count > 0,
        ));
    }

    /**
     * Health of the smallest stack in a section, ignoring empty ones.
     *
     * @param list<\App\Service\Stacker\StackLine> $lines Section lines.
     * @return float
     */
    private function lowestStackHealth(array $lines): float
    {
        $lowest = 0.0;
        foreach ($lines as $line) {
            $health = $line->stackHealth();
            if ($health <= 0.0) {
                continue;
            }
            $lowest = $lowest === 0.0 ? $health : min($lowest, $health);
        }

        return $lowest;
    }
}
