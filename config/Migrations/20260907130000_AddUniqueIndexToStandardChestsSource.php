<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Make standard_chests.source unique migration.
 *
 * Existing installations may already contain duplicated chest sources, which
 * would make the unique index creation fail and abort the upgrade. So the
 * duplicates are merged first (the most complete row is kept and the remaining
 * ones are removed) and, as a last resort, the index creation itself is allowed
 * to fail with a warning instead of breaking the update.
 */
class AddUniqueIndexToStandardChestsSource extends AbstractMigration
{
    /**
     * Name of the unique index created on standard_chests.source.
     *
     * @var string
     */
    public const INDEX_NAME = 'source_UNIQUE';

    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        $table = $this->table('standard_chests');

        if ($table->hasIndexByName(self::INDEX_NAME)) {
            return;
        }

        try {
            $removed = $this->mergeDuplicatedSources();
            if ($removed > 0) {
                $this->report(sprintf('Merged %d duplicated chest source(s) before creating the unique index.', $removed));
            }

            $table->addIndex(['source'], ['unique' => true, 'name' => self::INDEX_NAME])->update();
        } catch (Throwable $e) {
            // Never abort an upgrade because of leftover duplicates: the
            // application still blocks new duplicates through the ORM rules.
            $this->report('WARNING: could not create the unique index on standard_chests.source: ' . $e->getMessage());
            $this->report('WARNING: the upgrade continues, but duplicated chest sources are only blocked by the application.');
            $this->report('WARNING: remove the remaining duplicated sources and run `bin/cake.php migrations rollback` '
                . 'followed by `bin/cake.php migrations migrate` to create the index.');
        }
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        $table = $this->table('standard_chests');

        if ($table->hasIndexByName(self::INDEX_NAME)) {
            $table->removeIndexByName(self::INDEX_NAME)->update();
        }
    }

    /**
     * Collapse each group of rows sharing the same source into a single row.
     *
     * The kept row is the most complete one (alias first, then the highest
     * score) and any information only present in the discarded rows is copied
     * over to it, so no scoring setup is lost during the upgrade.
     *
     * @return int Number of removed rows.
     */
    protected function mergeDuplicatedSources(): int
    {
        // Grouping by the column itself uses the very same collation the unique
        // index will use, so every collision is caught here.
        $groups = $this->fetchAll(
            'SELECT GROUP_CONCAT(id) AS ids FROM standard_chests GROUP BY source HAVING COUNT(*) > 1'
        );

        $removed = 0;

        foreach ($groups as $group) {
            $ids = array_filter(array_map('intval', explode(',', (string)$group['ids'])));
            if (count($ids) < 2) {
                continue;
            }

            $rows = $this->fetchAll(
                'SELECT id, source, alias, score, monster, qty_chest FROM standard_chests '
                . 'WHERE id IN (' . implode(',', $ids) . ') ORDER BY id'
            );

            $keeper = $this->pickKeeper($rows);
            $merged = $this->mergeValues($keeper, $rows);

            if ($merged !== []) {
                $assignments = [];
                foreach ($merged as $column => $value) {
                    $assignments[] = sprintf('%s = %s', $column, $this->quote($value));
                }
                $this->execute(sprintf(
                    'UPDATE standard_chests SET %s WHERE id = %d',
                    implode(', ', $assignments),
                    (int)$keeper['id']
                ));
            }

            $discarded = array_diff($ids, [(int)$keeper['id']]);
            $this->execute(sprintf(
                'DELETE FROM standard_chests WHERE id IN (%s)',
                implode(',', $discarded)
            ));
            $removed += count($discarded);
        }

        return $removed;
    }

    /**
     * Choose which row of a duplicated group survives.
     *
     * @param array<array<string, mixed>> $rows Rows sharing the same source.
     * @return array<string, mixed>
     */
    protected function pickKeeper(array $rows): array
    {
        usort($rows, function (array $a, array $b): int {
            $aliasA = trim((string)($a['alias'] ?? '')) !== '' ? 1 : 0;
            $aliasB = trim((string)($b['alias'] ?? '')) !== '' ? 1 : 0;
            if ($aliasA !== $aliasB) {
                return $aliasB <=> $aliasA;
            }

            $scoreA = (int)($a['score'] ?? 0);
            $scoreB = (int)($b['score'] ?? 0);
            if ($scoreA !== $scoreB) {
                return $scoreB <=> $scoreA;
            }

            return (int)$a['id'] <=> (int)$b['id'];
        });

        return $rows[0];
    }

    /**
     * Collect values worth copying from the discarded rows into the kept one.
     *
     * @param array<string, mixed> $keeper The row that survives.
     * @param array<array<string, mixed>> $rows All rows of the group.
     * @return array<string, mixed> Columns to update on the kept row.
     */
    protected function mergeValues(array $keeper, array $rows): array
    {
        $merged = [];

        foreach ($rows as $row) {
            if ((int)$row['id'] === (int)$keeper['id']) {
                continue;
            }

            $alias = trim((string)($row['alias'] ?? ''));
            if ($alias !== '' && trim((string)($keeper['alias'] ?? '')) === '' && !isset($merged['alias'])) {
                $merged['alias'] = $alias;
            }

            if ((int)$row['score'] > (int)($merged['score'] ?? $keeper['score'])) {
                $merged['score'] = (int)$row['score'];
            }

            if ((int)$row['monster'] > (int)($merged['monster'] ?? $keeper['monster'])) {
                $merged['monster'] = (int)$row['monster'];
            }

            if ($row['qty_chest'] !== null && $keeper['qty_chest'] === null && !isset($merged['qty_chest'])) {
                $merged['qty_chest'] = (int)$row['qty_chest'];
            }
        }

        return $merged;
    }

    /**
     * Quote a value for inline SQL usage.
     *
     * @param mixed $value Value to quote.
     * @return string
     */
    protected function quote(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value)) {
            return (string)$value;
        }

        return "'" . str_replace("'", "''", (string)$value) . "'";
    }

    /**
     * Print a message through the migration output when available.
     *
     * @param string $message Message to print.
     * @return void
     */
    protected function report(string $message): void
    {
        $output = $this->getOutput();
        if ($output !== null) {
            $output->writeln(' == ' . $message);

            return;
        }

        echo $message . PHP_EOL;
    }
}
