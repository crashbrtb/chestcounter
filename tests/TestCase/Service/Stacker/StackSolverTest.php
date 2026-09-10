<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Stacker;

use App\Model\Entity\Troop;
use App\Service\Stacker\BonusProfile;
use App\Service\Stacker\StackLine;
use App\Service\Stacker\StackPlan;
use App\Service\Stacker\StackRequest;
use App\Service\Stacker\StackSolver;
use Cake\TestSuite\TestCase;

/**
 * App\Service\Stacker\StackSolver Test Case
 *
 * The solver's contract is a set of invariants rather than a fixed set of
 * numbers, so these tests assert the invariants against the real catalogue and
 * against small hand-built cases.
 */
class StackSolverTest extends TestCase
{
    /**
     * The catalogue shipped with the application.
     *
     * @var list<\App\Model\Entity\Troop>
     */
    private array $catalogue = [];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $payload = json_decode((string)file_get_contents(CONFIG . 'data' . DS . 'troops.json'), true);
        foreach ($payload['troops'] as $row) {
            $this->catalogue[] = $this->makeTroop($row);
        }
    }

    /**
     * Build a Troop entity straight from a catalogue row.
     *
     * @param array<string, mixed> $row Catalogue row.
     * @return \App\Model\Entity\Troop
     */
    private function makeTroop(array $row): Troop
    {
        $row['bonuses'] = is_array($row['bonuses'] ?? null)
            ? json_encode($row['bonuses'])
            : ($row['bonuses'] ?? null);
        $row['enabled'] = $row['enabled'] ?? true;

        return new Troop($row, ['markNew' => false, 'markClean' => true, 'guard' => false]);
    }

    /**
     * A minimal unit, for tests that do not need the real catalogue.
     *
     * @param array<string, mixed> $overrides Fields to override.
     * @return \App\Model\Entity\Troop
     */
    private function unit(array $overrides = []): Troop
    {
        return $this->makeTroop($overrides + [
            'slug' => 'unit-' . uniqid(),
            'name' => 'Unit',
            'troop_class' => 'guardsman',
            'category' => 'melee',
            'unit_type' => 'human',
            'level' => 9,
            'strength' => 100,
            'health' => 300,
            'cost_pool' => 'leadership',
            'cost' => 1,
            'revival_gold' => 4,
            'revival_silver' => 40,
            'is_mercenary' => false,
            'is_temporary' => false,
            'double_damage_chance' => 0,
            'player_battle_multiplier' => 1,
            'bonuses' => [],
        ]);
    }

    /**
     * A profile with meaningful bonuses, so the strike-order rule is active.
     *
     * @return \App\Service\Stacker\BonusProfile
     */
    private function lateGameBonuses(): BonusProfile
    {
        return BonusProfile::fromArray([
            'health' => [
                'class' => ['guardsman' => 1000, 'specialist' => 1000, 'monster' => 450, 'engineer' => 800],
                'type' => ['beast' => 129, 'giant' => 125, 'dragon' => 125, 'elemental' => 127],
            ],
            'strength' => [
                'class' => ['guardsman' => 2000, 'specialist' => 2000, 'monster' => 900, 'engineer' => 1200],
                'type' => ['beast' => 129, 'giant' => 125, 'dragon' => 125, 'elemental' => 127],
            ],
            'strength_vs_epic_monsters' => 300,
        ]);
    }

    /**
     * Assert the two ordering promises the calculator makes.
     *
     * @param \App\Service\Stacker\StackPlan $plan Plan to inspect.
     * @param bool $strikeOrder Whether strength descent was requested.
     * @return void
     */
    private function assertOrderingHolds(StackPlan $plan, bool $strikeOrder = true): void
    {
        $previous = null;
        $anchor = null;

        foreach ($plan->lines() as $line) {
            $this->assertGreaterThan(
                0,
                $line->count,
                sprintf('%s was planned with no soldiers', $line->troop->name),
            );

            if ($previous !== null) {
                $this->assertLessThan(
                    $previous->stackHealth(),
                    $line->stackHealth(),
                    sprintf('%s must die after %s', $previous->troop->name, $line->troop->name),
                );
            }
            $previous = $line;

            if (!$strikeOrder || $line->troop->troop_class === 'engineer') {
                continue;
            }

            if ($anchor !== null) {
                $this->assertLessThan(
                    $anchor->stackStrength(),
                    $line->stackStrength(),
                    sprintf('%s must strike after %s', $anchor->troop->name, $line->troop->name),
                );
            }

            if (!$line->isSacrifice) {
                $anchor = $line;
            }
        }
    }

    /**
     * Assert no section spent more than it was given.
     *
     * @param \App\Service\Stacker\StackPlan $plan Plan to inspect.
     * @return void
     */
    private function assertWithinCaps(StackPlan $plan): void
    {
        foreach (array_keys($plan->sections()) as $section) {
            $this->assertLessThanOrEqual(
                $plan->offeredCap($section),
                $plan->usedCap($section),
                sprintf('the %s section overspent its army limit', $section),
            );
        }
    }

    /**
     * A full late-game march keeps both orders and spends its leadership.
     *
     * @return void
     */
    public function testLateGameMarchHoldsBothOrders(): void
    {
        $plan = (new StackSolver($this->catalogue))->solve(new StackRequest(
            leadershipCap: 1_000_000,
            dominanceCap: 4_000,
            authorityCap: 3_000,
            bonuses: $this->lateGameBonuses(),
            killOrder: ['E9', 'S8', 'G8', 'S9', 'G9', 'M8', 'M9'],
        ));

        $this->assertOrderingHolds($plan);
        $this->assertWithinCaps($plan);
        $this->assertSame(1_000_000, $plan->usedCap('army'));
        $this->assertNotEmpty($plan->section('monsters'));
        $this->assertNotEmpty($plan->section('mercenaries'));
    }

    /**
     * Each section must stay below the one that dies before it.
     *
     * @return void
     */
    public function testSectionsAreOrderedArmyThenMonstersThenMercenaries(): void
    {
        $plan = (new StackSolver($this->catalogue))->solve(new StackRequest(
            leadershipCap: 500_000,
            dominanceCap: 2_000,
            authorityCap: 2_000,
            bonuses: $this->lateGameBonuses(),
            killOrder: ['E9', 'S9', 'G9', 'M9'],
        ));

        $lowest = static fn (array $lines): float => min(array_map(
            static fn (StackLine $line): float => $line->stackHealth(),
            $lines,
        ));
        $highest = static fn (array $lines): float => max(array_map(
            static fn (StackLine $line): float => $line->stackHealth(),
            $lines,
        ));

        $this->assertLessThan($lowest($plan->section('army')), $highest($plan->section('monsters')));
        $this->assertLessThan($lowest($plan->section('monsters')), $highest($plan->section('mercenaries')));
    }

    /**
     * The requested kill order decides the order of the stacks.
     *
     * @return void
     */
    public function testKillOrderIsRespected(): void
    {
        $plan = (new StackSolver($this->catalogue))->solve(new StackRequest(
            leadershipCap: 200_000,
            bonuses: $this->lateGameBonuses(),
            killOrder: ['S9', 'G9'],
            categoryOrder: ['melee', 'mounted', 'ranged', 'flying'],
        ));

        $groups = array_values(array_unique(array_map(
            static fn (StackLine $line): string => $line->troop->group_code,
            $plan->section('army'),
        )));

        $this->assertSame(['S9', 'G9'], $groups);
        $this->assertOrderingHolds($plan);
    }

    /**
     * Reversing the kill order reverses the plan.
     *
     * @return void
     */
    public function testReversingTheKillOrderReversesThePlan(): void
    {
        $solver = new StackSolver($this->catalogue);
        $plan = $solver->solve(new StackRequest(
            leadershipCap: 200_000,
            bonuses: $this->lateGameBonuses(),
            killOrder: ['G9', 'S9'],
        ));

        $groups = array_values(array_unique(array_map(
            static fn (StackLine $line): string => $line->troop->group_code,
            $plan->section('army'),
        )));

        $this->assertSame(['G9', 'S9'], $groups);
        $this->assertOrderingHolds($plan);
    }

    /**
     * Excluded units never make it into the march.
     *
     * @return void
     */
    public function testExcludedUnitsAreLeftOut(): void
    {
        $plan = (new StackSolver($this->catalogue))->solve(new StackRequest(
            leadershipCap: 200_000,
            bonuses: $this->lateGameBonuses(),
            killOrder: ['G9'],
            excludedSlugs: ['corax-ii'],
        ));

        $slugs = array_map(
            static fn (StackLine $line): string => $line->troop->slug,
            $plan->section('army'),
        );

        $this->assertNotEmpty($slugs);
        $this->assertNotContains('corax-ii', $slugs);
    }

    /**
     * Scouts carry loot and never take part in the kill order.
     *
     * @return void
     */
    public function testScoutsAreNeverPlanned(): void
    {
        $plan = (new StackSolver($this->catalogue))->solve(new StackRequest(
            leadershipCap: 200_000,
            bonuses: $this->lateGameBonuses(),
            killOrder: StackRequest::DEFAULT_KILL_ORDER,
        ));

        foreach ($plan->lines() as $line) {
            $this->assertNotSame('scout', $line->troop->category);
        }
    }

    /**
     * With no army limits there is nothing to plan.
     *
     * @return void
     */
    public function testEmptyCapsProduceAnEmptyPlan(): void
    {
        $plan = (new StackSolver($this->catalogue))->solve(new StackRequest(
            bonuses: $this->lateGameBonuses(),
        ));

        $this->assertSame([], $plan->lines());
        $this->assertSame(0.0, $plan->totalStrength());
    }

    /**
     * A limit too small for a single soldier yields nothing rather than a
     * negative or fractional stack.
     *
     * @return void
     */
    public function testLimitSmallerThanOneSoldierYieldsNothing(): void
    {
        $troop = $this->unit(['cost' => 20, 'name' => 'Heavy', 'slug' => 'heavy']);

        $plan = (new StackSolver([$troop]))->solve(new StackRequest(
            leadershipCap: 5,
            killOrder: ['G9'],
        ));

        $this->assertSame([], $plan->section('army'));
    }

    /**
     * A single unit takes the whole limit and nothing is left over.
     *
     * @return void
     */
    public function testSingleUnitConsumesTheWholeLimit(): void
    {
        $troop = $this->unit(['cost' => 2, 'health' => 100, 'slug' => 'solo', 'name' => 'Solo']);

        $plan = (new StackSolver([$troop]))->solve(new StackRequest(
            leadershipCap: 1_000,
            killOrder: ['G9'],
        ));

        $lines = $plan->section('army');
        $this->assertCount(1, $lines);
        $this->assertSame(500, $lines[0]->count);
        $this->assertSame(1_000, $plan->usedCap('army'));
    }

    /**
     * Without strength bonuses the strike-order rule has nothing to balance, so
     * only the health order is enforced.
     *
     * @return void
     */
    public function testHealthOrderHoldsWithoutStrengthBonuses(): void
    {
        $plan = (new StackSolver($this->catalogue))->solve(new StackRequest(
            leadershipCap: 300_000,
            bonuses: new BonusProfile(),
            killOrder: ['S9', 'G9'],
        ));

        $this->assertNotEmpty($plan->section('army'));
        $this->assertOrderingHolds($plan, strikeOrder: false);
    }

    /**
     * Turning the strike-order rule off still leaves a valid kill order.
     *
     * @return void
     */
    public function testStrikeOrderCanBeDisabled(): void
    {
        $plan = (new StackSolver($this->catalogue))->solve(new StackRequest(
            leadershipCap: 300_000,
            bonuses: $this->lateGameBonuses(),
            killOrder: ['S9', 'G9'],
            enforceStrikeOrder: false,
        ));

        $this->assertNotEmpty($plan->section('army'));
        $this->assertOrderingHolds($plan, strikeOrder: false);
    }

    /**
     * The number of enemy stacks decides which ranks are sacrifices.
     *
     * @return void
     */
    public function testEnemyStackCountDrivesSacrificeSlots(): void
    {
        $solver = new StackSolver($this->catalogue);
        $request = new StackRequest(
            leadershipCap: 300_000,
            bonuses: $this->lateGameBonuses(),
            killOrder: ['S9', 'G9'],
            enemyStackCount: 3,
        );

        $sacrifices = [];
        foreach ($solver->solve($request)->section('army') as $line) {
            if ($line->isSacrifice) {
                $sacrifices[] = $line->rank;
            }
        }

        $this->assertSame([0, 3, 6], array_slice($sacrifices, 0, 3));
    }

    /**
     * Setting no enemy stacks means no rank is treated as a sacrifice.
     *
     * @return void
     */
    public function testZeroEnemyStacksMeansNoSacrifices(): void
    {
        $plan = (new StackSolver($this->catalogue))->solve(new StackRequest(
            leadershipCap: 300_000,
            bonuses: $this->lateGameBonuses(),
            killOrder: ['S9', 'G9'],
            enemyStackCount: 0,
        ));

        foreach ($plan->lines() as $line) {
            $this->assertFalse($line->isSacrifice);
        }
    }

    /**
     * The mercenary roster follows the player's best guardsman tier: a tier 9
     * account is offered the tier 6-9 mercenaries and none of the lower ones.
     *
     * @return void
     */
    public function testMercenariesAreLimitedToTheGuardsmenTierOnOffer(): void
    {
        $solver = new StackSolver($this->catalogue);

        $tiers = static function (StackPlan $plan): array {
            $seen = [];
            foreach ($plan->section('mercenaries') as $line) {
                foreach ($line->troop->guardsmen_tier_list as $tier) {
                    $seen[$tier] = true;
                }
            }
            ksort($seen);

            return array_keys($seen);
        };

        $top = $solver->solve(new StackRequest(
            leadershipCap: 200_000,
            authorityCap: 3_000,
            bonuses: $this->lateGameBonuses(),
            killOrder: ['S9', 'G9'],
        ));
        $this->assertNotEmpty($top->section('mercenaries'));
        $this->assertSame([6, 7, 8, 9], $tiers($top));

        $mid = $solver->solve(new StackRequest(
            leadershipCap: 200_000,
            authorityCap: 3_000,
            bonuses: $this->lateGameBonuses(),
            killOrder: ['S5', 'G5'],
        ));
        $this->assertNotEmpty($mid->section('mercenaries'));
        $this->assertSame([5], $tiers($mid));
    }

    /**
     * With no guardsmen in the march there is no mercenary roster to draw on.
     *
     * @return void
     */
    public function testNoGuardsmenMeansNoMercenaries(): void
    {
        $plan = (new StackSolver($this->catalogue))->solve(new StackRequest(
            leadershipCap: 200_000,
            authorityCap: 3_000,
            bonuses: $this->lateGameBonuses(),
            killOrder: ['S9'],
        ));

        $this->assertNotEmpty($plan->section('army'));
        $this->assertSame([], $plan->section('mercenaries'));
    }

    /**
     * Monsters spend dominance, not leadership.
     *
     * @return void
     */
    public function testMonstersSpendDominanceOnly(): void
    {
        $plan = (new StackSolver($this->catalogue))->solve(new StackRequest(
            leadershipCap: 100_000,
            dominanceCap: 1_500,
            bonuses: $this->lateGameBonuses(),
            killOrder: ['G9', 'M9'],
        ));

        foreach ($plan->section('army') as $line) {
            $this->assertSame('leadership', $line->troop->cost_pool);
        }
        foreach ($plan->section('monsters') as $line) {
            $this->assertSame('dominance', $line->troop->cost_pool);
        }
        $this->assertLessThanOrEqual(1_500, $plan->usedCap('monsters'));
    }
}
