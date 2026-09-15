<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Let the EventUploader register a game tournament by itself.
 *
 * The game identifies every tournament result in the Journal with a unique id,
 * and every tournament with a type (for example `1024:1`). With both stored on
 * the event, the uploader can create the event the first time it sees a result
 * and send the same result again without creating a second one; and a new
 * tournament of a known type takes its name and rewards from the previous one.
 *
 * The game never sends the tournament's name: it is looked up on the client
 * from the type, so the name is typed once per type and remembered through
 * the events that carry it.
 *
 * Column comments carry no commas or semicolons (see CreateEventImports).
 */
class AddGameTournamentIdentity extends AbstractMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        $events = $this->table('events');
        if ($events->hasColumn('game_result_uid')) {
            return;
        }

        $events
            ->addColumn('game_result_uid', 'string', [
                'limit' => 64,
                'null' => true,
                'default' => null,
                'comment' => 'Id of the tournament result in the game Journal',
            ])
            ->addColumn('game_tournament_key', 'string', [
                'limit' => 32,
                'null' => true,
                'default' => null,
                'comment' => 'Tournament type in the game such as 1024:1',
            ])
            ->addIndex(['game_result_uid'], ['unique' => true, 'name' => 'events_game_result_uid_unique'])
            ->addIndex(['game_tournament_key'], ['name' => 'events_game_tournament_key'])
            ->update();
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        $events = $this->table('events');
        if (!$events->hasColumn('game_result_uid')) {
            return;
        }

        $events
            ->removeIndexByName('events_game_result_uid_unique')
            ->removeIndexByName('events_game_tournament_key')
            ->removeColumn('game_result_uid')
            ->removeColumn('game_tournament_key')
            ->update();
    }
}
