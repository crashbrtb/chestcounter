<?php
declare(strict_types=1);

namespace App\Service\Stacker;

/**
 * The finished march: three sections, each spending its own army limit, in the
 * order the stacks will be lost.
 */
class StackPlan
{
    /**
     * @param array<string, list<\App\Service\Stacker\StackLine>> $sections Lines
     *   keyed by section name (army / monsters / mercenaries).
     * @param array<string, int> $caps Army limit offered per section.
     * @param list<string> $warnings Anything the player should know about the
     *   result, such as limits that could not be spent.
     */
    public function __construct(
        private readonly array $sections = [],
        private readonly array $caps = [],
        private readonly array $warnings = [],
    ) {
    }

    /**
     * All lines of one section, in kill order.
     *
     * @param string $section Section name.
     * @return list<\App\Service\Stacker\StackLine>
     */
    public function section(string $section): array
    {
        return $this->sections[$section] ?? [];
    }

    /**
     * Every section, keyed by name.
     *
     * @return array<string, list<\App\Service\Stacker\StackLine>>
     */
    public function sections(): array
    {
        return $this->sections;
    }

    /**
     * Every line of every section, in overall kill order.
     *
     * @return list<\App\Service\Stacker\StackLine>
     */
    public function lines(): array
    {
        return array_merge(...array_values($this->sections ?: [[]]));
    }

    /**
     * Messages worth surfacing next to the result.
     *
     * @return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    /**
     * Army limit consumed by a section.
     *
     * @param string $section Section name.
     * @return int
     */
    public function usedCap(string $section): int
    {
        $used = 0;
        foreach ($this->section($section) as $line) {
            $used += $line->costUsed();
        }

        return $used;
    }

    /**
     * Army limit offered to a section.
     *
     * @param string $section Section name.
     * @return int
     */
    public function offeredCap(string $section): int
    {
        return $this->caps[$section] ?? 0;
    }

    /**
     * Total effective strength of the whole march.
     *
     * @return float
     */
    public function totalStrength(): float
    {
        $total = 0.0;
        foreach ($this->lines() as $line) {
            $total += $line->stackStrength();
        }

        return $total;
    }

    /**
     * Total effective health of the whole march.
     *
     * @return float
     */
    public function totalHealth(): float
    {
        $total = 0.0;
        foreach ($this->lines() as $line) {
            $total += $line->stackHealth();
        }

        return $total;
    }

    /**
     * Gold needed to revive every unit in the march.
     *
     * @return int
     */
    public function totalRevivalGold(): int
    {
        $total = 0;
        foreach ($this->lines() as $line) {
            $total += $line->revivalGold();
        }

        return $total;
    }

    /**
     * Serialize for the JSON endpoint.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $sections = [];
        foreach ($this->sections as $name => $lines) {
            $sections[$name] = [
                'offered_cap' => $this->offeredCap($name),
                'used_cap' => $this->usedCap($name),
                'lines' => array_map(fn (StackLine $line): array => $line->toArray(), $lines),
            ];
        }

        return [
            'sections' => $sections,
            'totals' => [
                'health' => $this->totalHealth(),
                'strength' => $this->totalStrength(),
                'revival_gold' => $this->totalRevivalGold(),
            ],
            'warnings' => $this->warnings,
        ];
    }
}
