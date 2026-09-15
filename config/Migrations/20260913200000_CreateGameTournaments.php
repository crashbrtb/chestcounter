<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

/**
 * The catalogue of the game's tournaments, and the clan roster they reveal.
 *
 * - `game_tournaments`   one row per tournament type of the game (the first
 *                        number of the type the game sends, e.g. 1024 in
 *                        `1024:1`). The name is filled in by the tournament
 *                        mapper, which reads it off the Journal; the image and
 *                        the duration in days are set by an administrator. The
 *                        duration is what an event's start date is derived from.
 * - `events`             linked to the catalogue.
 * - `event_imports`      remembers when its ranking was applied to the members
 *                        table, so an older tournament sent late cannot undo
 *                        what a newer one established.
 * - `members.power`      widened: players pass the 2.1 billion a signed integer holds.
 *
 * Column comments on altered tables carry no commas or semicolons (see CreateEventImports).
 */
class CreateGameTournaments extends AbstractMigration
{
    /**
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
        if (!$this->hasTable('game_tournaments')) {
            $this->table('game_tournaments', self::TABLE_OPTIONS)
                ->addColumn('game_type', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'comment' => 'Tournament type id in the game: 1024 in the key 1024:1',
                ])
                ->addColumn('name', 'string', ['limit' => 120, 'null' => true, 'default' => null])
                ->addColumn('name_source', 'string', [
                    'limit' => 16,
                    'null' => true,
                    'default' => null,
                    'comment' => 'journal | uploader | manual - a manual name is never overwritten',
                ])
                ->addColumn('duration_days', 'integer', [
                    'null' => true,
                    'default' => null,
                    'signed' => false,
                    'comment' => 'How long the tournament runs. Unknown means one day',
                ])
                ->addColumn('image_mime', 'string', ['limit' => 60, 'null' => true, 'default' => null])
                ->addColumn('image', 'blob', [
                    'limit' => MysqlAdapter::BLOB_MEDIUM,
                    'null' => true,
                    'default' => null,
                ])
                ->addColumn('last_variant', 'string', ['limit' => 32, 'null' => true, 'default' => null])
                ->addColumn('first_seen_at', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('last_seen_at', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
                ->addIndex(['game_type'], ['unique' => true, 'name' => 'game_tournaments_type_unique'])
                ->create();
        }

        $events = $this->table('events');
        if (!$events->hasColumn('game_tournament_id')) {
            $events
                ->addColumn('game_tournament_id', 'integer', [
                    'null' => true,
                    'default' => null,
                    'comment' => 'Catalogue entry of a game event',
                ])
                ->addIndex(['game_tournament_id'], ['name' => 'events_game_tournament'])
                ->update();
        }

        $imports = $this->table('event_imports');
        if (!$imports->hasColumn('roster_applied_at')) {
            $imports
                ->addColumn('roster_applied_at', 'datetime', [
                    'null' => true,
                    'default' => null,
                    'comment' => 'When this ranking updated the members table',
                ])
                ->addColumn('roster_summary', 'text', ['null' => true, 'default' => null])
                ->update();
        }

        // Only MySQL needs the widening: SQLite integers are already 64 bits.
        if ($this->getAdapter()->getAdapterType() === 'mysql') {
            $this->table('members')
                ->changeColumn('power', 'biginteger', ['null' => false, 'default' => 0, 'signed' => true])
                ->update();
        }

        // Existing game events get their catalogue entry.
        $rows = $this->fetchAll(
            "SELECT id, name, game_tournament_key, ends_at FROM events "
            . "WHERE game_tournament_key IS NOT NULL AND game_tournament_id IS NULL ORDER BY ends_at"
        );
        foreach ($rows as $row) {
            $type = (int)explode(':', (string)$row['game_tournament_key'])[0];
            if ($type <= 0) {
                continue;
            }
            $existing = $this->fetchRow('SELECT id FROM game_tournaments WHERE game_type = ' . $type);
            if (!$existing) {
                $this->table('game_tournaments')->insert([
                    'game_type' => $type,
                    'name' => $row['name'],
                    'name_source' => 'uploader',
                    'first_seen_at' => $row['ends_at'],
                    'last_seen_at' => $row['ends_at'],
                    'created' => date('Y-m-d H:i:s'),
                    'modified' => date('Y-m-d H:i:s'),
                ])->saveData();
                $existing = $this->fetchRow('SELECT id FROM game_tournaments WHERE game_type = ' . $type);
            }
            $this->execute(sprintf('UPDATE events SET game_tournament_id = %d WHERE id = %d', $existing['id'], $row['id']));
        }
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        $imports = $this->table('event_imports');
        if ($imports->hasColumn('roster_applied_at')) {
            $imports->removeColumn('roster_applied_at')->removeColumn('roster_summary')->update();
        }

        $events = $this->table('events');
        if ($events->hasColumn('game_tournament_id')) {
            $events->removeIndexByName('events_game_tournament')->removeColumn('game_tournament_id')->update();
        }

        if ($this->hasTable('game_tournaments')) {
            $this->table('game_tournaments')->drop()->save();
        }

        if ($this->getAdapter()->getAdapterType() === 'mysql') {
            $this->table('members')
                ->changeColumn('power', 'integer', ['null' => false, 'default' => 0, 'signed' => true])
                ->update();
        }
    }
}
