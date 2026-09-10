<?php
declare(strict_types=1);

namespace App\Service\Stacker;

/**
 * Everything the solver needs to plan a march: the army limits to spend, the
 * player's bonuses, and the order in which the stacks should die.
 */
class StackRequest
{
    /**
     * Default order the stacks should be lost in.
     *
     * Weakest first so the strongest units survive the most rounds and land the
     * most hits: sacrificial engineers, then each level from the bottom up with
     * specialists ahead of guardsmen, then monsters. Mercenaries are not listed
     * because they are bought with authority and are placed after the monsters
     * by the section ceiling.
     *
     * @var list<string>
     */
    public const DEFAULT_KILL_ORDER = [
        'E9', 'E8', 'E7', 'E6', 'E5',
        'S1', 'G1', 'S2', 'G2', 'S3', 'G3', 'S4', 'G4', 'S5', 'G5',
        'S6', 'G6', 'S7', 'G7', 'S8', 'G8', 'S9', 'G9',
        'M1', 'M2', 'M3', 'M4', 'M5', 'M6', 'M7', 'M8', 'M9',
    ];

    /**
     * Default order the categories die in within one level.
     *
     * @var list<string>
     */
    public const DEFAULT_CATEGORY_ORDER = ['melee', 'mounted', 'ranged', 'flying'];

    /**
     * @param int $leadershipCap Leadership available for guardsmen, specialists
     *   and engineer corps.
     * @param int $dominanceCap Dominance available for monsters.
     * @param int $authorityCap Authority available for mercenaries.
     * @param \App\Service\Stacker\BonusProfile $bonuses The player's researched bonuses.
     * @param list<string> $killOrder Group codes (G9, S8, M7, E9...) in the
     *   order they should be lost. Groups absent from the catalogue or from the
     *   player's selection are skipped.
     * @param list<string> $categoryOrder Category order within a level.
     * @param list<string> $excludedSlugs Units the player does not own or does
     *   not want in the march.
     * @param int $enemyStackCount How many stacks the target fields. The first
     *   death of each round is a sacrifice that will not strike, so this drives
     *   which ranks are exempt from the strike-order rule. Use 4 for a standard
     *   Epic Monster; set it to 0 to treat no rank as a sacrifice.
     * @param bool $enforceStrikeOrder Whether stack strength must descend along
     *   with stack health, so each stack strikes before it dies. Turn it off for
     *   targets that do not interleave strikes between kills.
     * @param float $sectionGap Fraction of the previous section's lowest stack
     *   health that the next section's highest stack may reach. 0.95 leaves a
     *   5% gap between army, monsters and mercenaries.
     */
    public function __construct(
        public readonly int $leadershipCap = 0,
        public readonly int $dominanceCap = 0,
        public readonly int $authorityCap = 0,
        public readonly BonusProfile $bonuses = new BonusProfile(),
        public readonly array $killOrder = self::DEFAULT_KILL_ORDER,
        public readonly array $categoryOrder = self::DEFAULT_CATEGORY_ORDER,
        public readonly array $excludedSlugs = [],
        public readonly int $enemyStackCount = 4,
        public readonly bool $enforceStrikeOrder = true,
        public readonly float $sectionGap = 0.95,
    ) {
    }

    /**
     * Build a request from a submitted form.
     *
     * @param array<string, mixed> $data Raw request data.
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $list = static function (mixed $value, array $default): array {
            if (is_string($value)) {
                $value = array_filter(array_map('trim', explode(',', $value)));
            }

            return is_array($value) && $value !== [] ? array_values($value) : $default;
        };

        $gap = (float)($data['section_gap'] ?? 0.95);

        return new self(
            leadershipCap: max(0, (int)($data['leadership_cap'] ?? 0)),
            dominanceCap: max(0, (int)($data['dominance_cap'] ?? 0)),
            authorityCap: max(0, (int)($data['authority_cap'] ?? 0)),
            bonuses: BonusProfile::fromArray(is_array($data['bonuses'] ?? null) ? $data['bonuses'] : []),
            killOrder: $list($data['kill_order'] ?? null, self::DEFAULT_KILL_ORDER),
            categoryOrder: $list($data['category_order'] ?? null, self::DEFAULT_CATEGORY_ORDER),
            excludedSlugs: $list($data['excluded'] ?? null, []),
            enemyStackCount: max(0, (int)($data['enemy_stack_count'] ?? 4)),
            enforceStrikeOrder: (bool)($data['enforce_strike_order'] ?? true),
            sectionGap: $gap > 0 && $gap <= 1 ? $gap : 0.95,
        );
    }
}
