<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

/**
 * Create the tournament event module.
 *
 * An event is a window of time with a rule for what counts, announced to the
 * players with a prize and somebody to talk to when it ends. Four tables:
 *
 * - `events`          the event itself, its window, its rule and its banner
 * - `event_chests`    which chest sources count, when the rule is "custom chests"
 * - `event_standings` the frozen result, written when an event is closed
 * - `event_assets`    the default banners shown on the score page
 *
 * Standings are stored rather than always recomputed because `collected_chests`
 * is purged on a retention schedule (see PurgeCollectedChestsCommand): an event
 * from four months ago has no rows left to add up, so the result has to be kept
 * at the moment the event closes or the history is empty.
 */
class CreateEvents extends AbstractMigration
{
    /**
     * Character set for every table created here.
     *
     * Stated explicitly because the database default cannot be relied on: at
     * least one deployment defaults to latin1, and these tables hold player
     * names and chest sources, which are full of non-ASCII characters. Matches
     * what the rest of the schema already uses.
     *
     * @var array<string, string>
     */
    private const TABLE_OPTIONS = [
        'encoding' => 'utf8mb4',
        'collation' => 'utf8mb4_general_ci',
    ];

    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        $this->dropLegacyEventsStub();

        if (!$this->hasTable('events')) {
            $this->table('events', self::TABLE_OPTIONS)
                ->addColumn('event_number', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'comment' => 'Human-facing sequential identifier, assigned on creation',
                ])
                ->addColumn('name', 'string', ['limit' => 120, 'null' => false])
                ->addColumn('description', 'text', ['null' => true, 'default' => null])
                ->addColumn('criteria', 'string', [
                    'limit' => 32,
                    'null' => false,
                    'comment' => 'chest_count | chest_score | epic_monster | custom_chests',
                ])
                ->addColumn('custom_metric', 'string', [
                    'limit' => 16,
                    'null' => false,
                    'default' => 'score',
                    'comment' => 'For custom_chests only: rank by score | count of the chosen chests',
                ])
                ->addColumn('starts_at', 'datetime', [
                    'null' => false,
                    'comment' => 'UTC. Everything in this application is stored and compared in UTC.',
                ])
                ->addColumn('ends_at', 'datetime', ['null' => false, 'comment' => 'UTC'])
                ->addColumn('prize', 'text', [
                    'null' => false,
                    'comment' => 'What the winner gets, in the words the players will read',
                ])
                ->addColumn('contact_player', 'string', [
                    'limit' => 120,
                    'null' => false,
                    'comment' => 'Who to look for once the event is over',
                ])
                ->addColumn('banner_mime', 'string', ['limit' => 60, 'null' => true, 'default' => null])
                ->addColumn('banner_image', 'blob', [
                    'limit' => MysqlAdapter::BLOB_MEDIUM,
                    'null' => true,
                    'default' => null,
                    'comment' => 'Per-event banner; falls back to the event-live default asset',
                ])
                ->addColumn('status', 'string', [
                    'limit' => 20,
                    'null' => false,
                    'default' => 'scheduled',
                    'comment' => 'scheduled | cancelled. Running/finished is derived from the window.',
                ])
                ->addColumn('finalized_at', 'datetime', [
                    'null' => true,
                    'default' => null,
                    'comment' => 'When the standings below were frozen',
                ])
                ->addColumn('created_by', 'integer', ['null' => true, 'default' => null])
                ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
                ->addIndex(['event_number'], ['unique' => true, 'name' => 'events_number_unique'])
                ->addIndex(['starts_at', 'ends_at'], ['name' => 'events_window'])
                ->addIndex(['status'], ['name' => 'events_status'])
                ->create();
        }

        if (!$this->hasTable('event_chests')) {
            $this->table('event_chests', self::TABLE_OPTIONS)
                ->addColumn('event_id', 'integer', ['null' => false])
                ->addColumn('standard_chest_id', 'integer', ['null' => false])
                ->addColumn('source', 'string', [
                    'limit' => 50,
                    'null' => false,
                    'comment' => 'Chest source as it was when picked, so a later rename cannot silently '
                        . 'change what an event counted',
                ])
                ->addIndex(
                    ['event_id', 'standard_chest_id'],
                    ['unique' => true, 'name' => 'event_chests_unique']
                )
                ->addIndex(['event_id'], ['name' => 'event_chests_event'])
                ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        if (!$this->hasTable('event_standings')) {
            $this->table('event_standings', self::TABLE_OPTIONS)
                ->addColumn('event_id', 'integer', ['null' => false])
                ->addColumn('position', 'integer', ['null' => false])
                ->addColumn('player', 'string', ['limit' => 50, 'null' => false])
                ->addColumn('points', 'biginteger', [
                    'null' => false,
                    'default' => 0,
                    'comment' => 'Value of the event criteria for this player',
                ])
                ->addColumn('chest_count', 'integer', ['null' => false, 'default' => 0])
                ->addColumn('chest_score', 'biginteger', ['null' => false, 'default' => 0])
                ->addColumn('participation', 'decimal', [
                    'precision' => 6,
                    'scale' => 2,
                    'null' => false,
                    'default' => 0,
                    'comment' => 'Percent of the event total contributed by this player',
                ])
                ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
                ->addIndex(['event_id', 'player'], ['unique' => true, 'name' => 'event_standings_unique'])
                ->addIndex(['event_id', 'position'], ['name' => 'event_standings_order'])
                ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        if (!$this->hasTable('event_assets')) {
            $this->table('event_assets', self::TABLE_OPTIONS)
                ->addColumn('slug', 'string', [
                    'limit' => 40,
                    'null' => false,
                    'comment' => 'event-live | no-event',
                ])
                ->addColumn('label', 'string', ['limit' => 160, 'null' => false])
                ->addColumn('mime', 'string', ['limit' => 60, 'null' => false, 'default' => 'image/png'])
                ->addColumn('image', 'blob', [
                    'limit' => MysqlAdapter::BLOB_MEDIUM,
                    'null' => false,
                ])
                ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
                ->addIndex(['slug'], ['unique' => true, 'name' => 'event_assets_slug_unique'])
                ->create();
        }

        $this->normalizeCharset();
        $this->seedDefaultBanners();
    }

    /**
     * Force the four tables to utf8mb4.
     *
     * Creating them with the options above covers a fresh run, but an install
     * that already ran an earlier version of this migration inherited the
     * database default, which may be latin1 - and inserting a player whose name
     * is not plain ASCII then fails outright. Converting is idempotent, so it is
     * safe to do on every run.
     *
     * @return void
     */
    private function normalizeCharset(): void
    {
        if ($this->getAdapter()->getAdapterType() !== 'mysql') {
            return;
        }

        foreach (['events', 'event_chests', 'event_standings', 'event_assets'] as $table) {
            $this->execute(sprintf(
                'ALTER TABLE `%s` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci',
                $table
            ));
        }
    }

    /**
     * Remove the placeholder `events` table the initial schema shipped.
     *
     * That table was three columns wide (id, start_date, end_date), was written
     * to by nothing, and was a stub for exactly the feature this migration
     * implements. It has to go, or the real table can never be created.
     *
     * Recognised by shape rather than by name alone, and refused outright if it
     * holds rows: a table with data in it is not the stub, whatever it looks like.
     *
     * @return void
     * @throws \RuntimeException When the old table unexpectedly holds data.
     */
    private function dropLegacyEventsStub(): void
    {
        if (!$this->hasTable('events')) {
            return;
        }

        $table = $this->table('events');
        if ($table->hasColumn('event_number') || !$table->hasColumn('start_date')) {
            return;
        }

        $rows = $this->fetchRow('SELECT COUNT(*) AS total FROM events');
        if ((int)($rows['total'] ?? 0) > 0) {
            throw new RuntimeException(
                'The legacy `events` table holds rows and will not be replaced automatically. '
                . 'Inspect and clear it before running this migration.'
            );
        }

        $table->drop()->save();
    }

    /**
     * Insert the shipped banner artwork.
     *
     * The bytes come from config/data/event_banner_defaults.php rather than being
     * drawn here: the migration must not depend on GD or on a font being present
     * on whatever machine runs the install.
     *
     * Existing rows are left alone, so re-running the migration never overwrites
     * a banner an administrator has uploaded.
     *
     * @return void
     */
    private function seedDefaultBanners(): void
    {
        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'event_banner_defaults.php';
        if (!is_file($file)) {
            return;
        }

        $defaults = require $file;
        $now = date('Y-m-d H:i:s');

        foreach ($defaults as $slug => $asset) {
            $existing = $this->fetchRow(sprintf(
                "SELECT id FROM event_assets WHERE slug = '%s'",
                $slug
            ));
            if ($existing) {
                continue;
            }

            $this->table('event_assets')->insert([
                'slug' => $slug,
                'label' => $asset['label'],
                'mime' => $asset['mime'],
                'image' => base64_decode($asset['base64']),
                'created' => $now,
                'modified' => $now,
            ])->saveData();
        }
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        foreach (['event_standings', 'event_chests', 'event_assets', 'events'] as $table) {
            if ($this->hasTable($table)) {
                $this->table($table)->drop()->save();
            }
        }
    }
}
