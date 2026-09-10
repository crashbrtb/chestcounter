<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add screenshot and review tracking to incomplete_chests migration.
 *
 * A chest the collector could not read is not lost any more: the capture of the
 * panel is kept with the row, so a person can read what the OCR could not and
 * finish the record by hand.
 *
 * The review columns exist so that work is not repeated. Once someone has
 * looked at a chest, the row says what was decided - corrected, and which
 * collected_chests row came out of it, or examined and not recoverable - and
 * the queue only ever shows what is still waiting.
 */
class AddScreenshotAndReviewToIncompleteChests extends AbstractMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        $table = $this->table('incomplete_chests');

        if (!$table->hasColumn('screenshot')) {
            $table->addColumn('screenshot', 'blob', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::BLOB_MEDIUM,
                'null' => true,
                'default' => null,
                'after' => 'source',
                'comment' => 'PNG of the chest panel as it was on screen, for a human to read',
            ]);
        }

        if (!$table->hasColumn('status')) {
            $table->addColumn('status', 'string', [
                'limit' => 20,
                'null' => false,
                'default' => 'pending',
                'after' => 'type',
                'comment' => 'pending | corrected | unresolved',
            ]);
        }

        if (!$table->hasColumn('collected_chest_id')) {
            $table->addColumn('collected_chest_id', 'integer', [
                'null' => true,
                'default' => null,
                'after' => 'status',
                'comment' => 'Row created in collected_chests when this one was corrected',
            ]);
        }

        if (!$table->hasColumn('review_notes')) {
            $table->addColumn('review_notes', 'string', [
                'limit' => 255,
                'null' => true,
                'default' => null,
                'after' => 'collected_chest_id',
                'comment' => 'Why it could not be corrected, when that is the outcome',
            ]);
        }

        if (!$table->hasColumn('reviewed_by')) {
            $table->addColumn('reviewed_by', 'integer', [
                'null' => true,
                'default' => null,
                'after' => 'review_notes',
                'comment' => 'users.id of whoever reviewed it',
            ]);
        }

        if (!$table->hasColumn('reviewed_at')) {
            $table->addColumn('reviewed_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'reviewed_by',
            ]);
        }

        $table->update();

        // The queue is always read as "what is still pending, oldest first".
        if (!$table->hasIndex(['status'])) {
            $table->addIndex(['status'], ['name' => 'idx_incomplete_chests_status'])->update();
        }
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        $table = $this->table('incomplete_chests');

        if ($table->hasIndex(['status'])) {
            $table->removeIndexByName('idx_incomplete_chests_status')->update();
        }

        foreach (['reviewed_at', 'reviewed_by', 'review_notes', 'collected_chest_id', 'status', 'screenshot'] as $column) {
            if ($table->hasColumn($column)) {
                $table->removeColumn($column);
            }
        }

        $table->update();
    }
}
