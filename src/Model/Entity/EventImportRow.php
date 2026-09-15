<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * One player of an uploaded ranking, as the administrator reviews it.
 *
 * @property int $id
 * @property int $event_import_id
 * @property int $position
 * @property int|null $game_player_id
 * @property string $raw_name
 * @property int $points
 * @property int|null $original_points
 * @property int|null $power
 * @property int|null $member_id
 * @property string $match_type
 * @property bool $eligible
 * @property \App\Model\Entity\Member|null $member
 */
class EventImportRow extends Entity
{
    /** Linked by the game's player id: certain. */
    public const MATCH_PLAYER_ID = 'player_id';
    /** Linked by an exact, unambiguous name: confirm when publishing. */
    public const MATCH_NAME = 'name';
    /** Linked through a registered name correction. */
    public const MATCH_MAPPING = 'mapping';
    /** Linked by hand on the review page. */
    public const MATCH_MANUAL = 'manual';
    /** Not linked to any member. */
    public const MATCH_NONE = 'none';

    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [];

    /**
     * The name the review page and the published result show.
     *
     * @return string
     */
    public function displayName(): string
    {
        return $this->member !== null ? (string)$this->member->player : (string)$this->raw_name;
    }
}
