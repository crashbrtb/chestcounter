<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

/**
 * Game tournament catalogue seed.
 *
 * Ships the tournament names and images the tournament mapper (EventUploader)
 * already found on this clan's Journal, so a fresh deployment starts with a
 * populated `game_tournaments` catalogue instead of an admin having to
 * calibrate and run the mapper again just to get names back.
 *
 * Idempotent per `game_type`: a row already in the table (mapped again since,
 * or edited by an admin) is left untouched.
 */
class GameTournamentsSeed extends AbstractSeed
{
    /**
     * Run the seed.
     */
    public function run(): void
    {
        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'game_tournament_defaults.php';
        if (!is_file($file)) {
            return;
        }

        $defaults = require $file;
        $now = date('Y-m-d H:i:s');
        // Table::insert()->saveData() binds every value as a plain string: on
        // SQLite that truncates a blob at its first null byte (a PNG's first
        // one is 8 bytes in), silently storing a mangled image. Inserting
        // through the connection directly, with the image typed as binary,
        // lets the query builder bind it as a blob on every driver.
        $connection = $this->getAdapter()->getConnection();

        foreach ($defaults as $gameType => $tournament) {
            $existing = $this->fetchRow(sprintf(
                'SELECT id FROM game_tournaments WHERE game_type = %d',
                $gameType
            ));
            if ($existing) {
                continue;
            }

            $connection->insert('game_tournaments', [
                'game_type' => $gameType,
                'name' => $tournament['name'],
                'name_source' => $tournament['name_source'],
                'duration_days' => $tournament['duration_days'],
                'image_mime' => $tournament['image_mime'],
                'image' => base64_decode($tournament['base64']),
                'first_seen_at' => $now,
                'last_seen_at' => $now,
                'created' => $now,
                'modified' => $now,
            ], ['image' => 'binary']);
        }
    }
}
