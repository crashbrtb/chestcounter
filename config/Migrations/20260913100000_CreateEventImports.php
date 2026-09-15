<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Tournaments whose scores come from the game instead of from collected chests.
 *
 * The clan gets daily prizes (pieces, coins, items) from in-game tournaments and
 * splits them among the players. The ranking is read from the game by the
 * EventUploader desktop tool and posted to the API; an administrator reviews it
 * and publishes the result with every player's share.
 *
 * - `members`                   game player id, and whether the account is an
 *                               administrative one that never gets a prize
 * - `events`                    `published_at`, set when an imported result goes public
 * - `event_rewards`             what an event hands out and how it is split
 * - `event_imports`             every ranking uploaded for an event, kept as history
 * - `event_import_rows`         the players of one upload, as reviewed
 * - `event_standings`           gains the columns a published import needs
 * - `event_reward_allocations`  how much of each reward each player receives
 * - `api_tokens`                personal tokens the uploader authenticates with
 *
 * Players are identified by the game's player id. Names are not unique: the
 * first real upload already had two different players called "Lion" in one clan.
 *
 * Column comments on tables this migration alters carry no commas or semicolons:
 * the SQLite adapter the test suite runs on rebuilds an altered table by
 * splitting its definition on them, and a rollback would fail.
 */
class CreateEventImports extends AbstractMigration
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
        $members = $this->table('members');
        if (!$members->hasColumn('game_player_id')) {
            $members
                ->addColumn('game_player_id', 'biginteger', [
                    'null' => true,
                    'default' => null,
                    'signed' => false,
                    'comment' => 'Player id inside the game which unlike the name is unique',
                ])
                ->addColumn('administrative_account', 'boolean', [
                    'null' => false,
                    'default' => false,
                    'comment' => 'Clan-owned account that takes part in rankings but never receives a prize',
                ])
                ->addIndex(['game_player_id'], ['unique' => true, 'name' => 'members_game_player_id_unique'])
                ->update();
        }

        $events = $this->table('events');
        if (!$events->hasColumn('published_at')) {
            $events
                ->addColumn('published_at', 'datetime', [
                    'null' => true,
                    'default' => null,
                    'after' => 'finalized_at',
                    'comment' => 'Imported events only: when the reviewed result was made public',
                ])
                ->update();
        }

        if (!$this->hasTable('event_rewards')) {
            $this->table('event_rewards', self::TABLE_OPTIONS)
                ->addColumn('event_id', 'integer', ['null' => false])
                ->addColumn('item_name', 'string', ['limit' => 120, 'null' => false])
                ->addColumn('quantity', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('rule', 'string', [
                    'limit' => 16,
                    'null' => false,
                    'comment' => 'equal | proportional',
                ])
                ->addColumn('min_points', 'biginteger', [
                    'null' => false,
                    'default' => 1,
                    'signed' => false,
                    'comment' => 'Players below this score take no part in the split',
                ])
                ->addColumn('remainder', 'string', [
                    'limit' => 16,
                    'null' => false,
                    'default' => 'top_ranked',
                    'comment' => 'What happens to units that do not divide evenly: top_ranked | keep',
                ])
                ->addColumn('sort', 'integer', ['null' => false, 'default' => 0])
                ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
                ->addIndex(['event_id', 'sort'], ['name' => 'event_rewards_event'])
                ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        if (!$this->hasTable('api_tokens')) {
            $this->table('api_tokens', self::TABLE_OPTIONS)
                ->addColumn('user_id', 'integer', ['null' => false])
                ->addColumn('name', 'string', ['limit' => 60, 'null' => false])
                ->addColumn('token_hash', 'char', [
                    'limit' => 64,
                    'null' => false,
                    'comment' => 'sha256 of the token, which is shown once and never stored',
                ])
                ->addColumn('prefix', 'string', ['limit' => 16, 'null' => false])
                ->addColumn('last_used_at', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('last_used_ip', 'string', ['limit' => 45, 'null' => true, 'default' => null])
                ->addColumn('expires_at', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('revoked_at', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
                ->addIndex(['token_hash'], ['unique' => true, 'name' => 'api_tokens_hash_unique'])
                ->addIndex(['user_id'], ['name' => 'api_tokens_user'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        if (!$this->hasTable('event_imports')) {
            $this->table('event_imports', self::TABLE_OPTIONS)
                ->addColumn('event_id', 'integer', ['null' => false])
                ->addColumn('user_id', 'integer', ['null' => true, 'default' => null])
                ->addColumn('api_token_id', 'integer', ['null' => true, 'default' => null])
                ->addColumn('status', 'string', [
                    'limit' => 16,
                    'null' => false,
                    'default' => 'draft',
                    'comment' => 'draft | superseded | published',
                ])
                ->addColumn('game_event_name', 'string', ['limit' => 120, 'null' => true, 'default' => null])
                ->addColumn('game_event_at', 'datetime', ['null' => true, 'default' => null])
                ->addColumn('capture_method', 'string', [
                    'limit' => 16,
                    'null' => false,
                    'comment' => 'packet | ocr | csv',
                ])
                ->addColumn('client_version', 'string', ['limit' => 32, 'null' => true, 'default' => null])
                ->addColumn('payload_hash', 'char', [
                    'limit' => 64,
                    'null' => false,
                    'comment' => 'sha256 of the uploaded ranking, so an identical re-upload is recognised',
                ])
                ->addColumn('row_count', 'integer', ['null' => false, 'default' => 0])
                ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
                ->addIndex(['event_id', 'status'], ['name' => 'event_imports_event_status'])
                ->addIndex(['event_id', 'payload_hash'], ['name' => 'event_imports_payload'])
                ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        if (!$this->hasTable('event_import_rows')) {
            $this->table('event_import_rows', self::TABLE_OPTIONS)
                ->addColumn('event_import_id', 'integer', ['null' => false])
                ->addColumn('position', 'integer', ['null' => false])
                ->addColumn('game_player_id', 'biginteger', ['null' => true, 'default' => null, 'signed' => false])
                ->addColumn('raw_name', 'string', [
                    'limit' => 60,
                    'null' => false,
                    'comment' => 'The name exactly as the game sent it',
                ])
                ->addColumn('points', 'biginteger', ['null' => false, 'default' => 0, 'signed' => false])
                ->addColumn('original_points', 'biginteger', [
                    'null' => true,
                    'default' => null,
                    'signed' => false,
                    'comment' => 'Set when an administrator corrected the points by hand',
                ])
                ->addColumn('power', 'biginteger', ['null' => true, 'default' => null, 'signed' => false])
                ->addColumn('member_id', 'integer', ['null' => true, 'default' => null])
                ->addColumn('match_type', 'string', [
                    'limit' => 16,
                    'null' => false,
                    'default' => 'none',
                    'comment' => 'player_id | name | mapping | manual | none',
                ])
                ->addColumn('eligible', 'boolean', ['null' => false, 'default' => true])
                ->addIndex(['event_import_id', 'position'], ['name' => 'event_import_rows_order'])
                ->addForeignKey('event_import_id', 'event_imports', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        $standings = $this->table('event_standings');
        if (!$standings->hasColumn('eligible')) {
            // Two players may share a name, so the old unique (event, player)
            // index cannot survive a published import.
            if ($standings->hasIndexByName('event_standings_unique')) {
                $standings->removeIndexByName('event_standings_unique')->update();
            }
            $this->table('event_standings')
                ->addColumn('member_id', 'integer', ['null' => true, 'default' => null])
                ->addColumn('game_player_id', 'biginteger', ['null' => true, 'default' => null, 'signed' => false])
                ->addColumn('power', 'biginteger', ['null' => true, 'default' => null, 'signed' => false])
                ->addColumn('eligible', 'boolean', [
                    'null' => false,
                    'default' => true,
                    'comment' => 'False for administrative accounts and anyone excluded from the prize',
                ])
                ->addIndex(['event_id', 'player'], ['name' => 'event_standings_player'])
                ->update();
        }

        if (!$this->hasTable('event_reward_allocations')) {
            $this->table('event_reward_allocations', self::TABLE_OPTIONS)
                ->addColumn('event_reward_id', 'integer', ['null' => false])
                ->addColumn('event_standing_id', 'integer', ['null' => false])
                ->addColumn('amount', 'integer', ['null' => false, 'default' => 0, 'signed' => false])
                ->addIndex(
                    ['event_reward_id', 'event_standing_id'],
                    ['unique' => true, 'name' => 'event_reward_allocations_unique']
                )
                ->addIndex(['event_standing_id'], ['name' => 'event_reward_allocations_standing'])
                ->addForeignKey('event_reward_id', 'event_rewards', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('event_standing_id', 'event_standings', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        foreach (['event_reward_allocations', 'event_import_rows', 'event_imports', 'api_tokens', 'event_rewards'] as $table) {
            if ($this->hasTable($table)) {
                $this->table($table)->drop()->save();
            }
        }

        $standings = $this->table('event_standings');
        if ($standings->hasColumn('eligible')) {
            $standings
                ->removeIndexByName('event_standings_player')
                ->removeColumn('member_id')
                ->removeColumn('game_player_id')
                ->removeColumn('power')
                ->removeColumn('eligible')
                ->update();
            // Only safe to restore when no event has two players with one name.
            $this->table('event_standings')
                ->addIndex(['event_id', 'player'], ['unique' => true, 'name' => 'event_standings_unique'])
                ->update();
        }

        $events = $this->table('events');
        if ($events->hasColumn('published_at')) {
            $events->removeColumn('published_at')->update();
        }

        $members = $this->table('members');
        if ($members->hasColumn('game_player_id')) {
            $members
                ->removeIndexByName('members_game_player_id_unique')
                ->removeColumn('game_player_id')
                ->removeColumn('administrative_account')
                ->update();
        }
    }
}
