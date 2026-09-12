<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * EventChest Entity
 *
 * One chest type an event counts, when the event's criteria is "custom chests".
 *
 * @property int $id
 * @property int $event_id
 * @property int $standard_chest_id
 * @property string $source
 * @property \App\Model\Entity\Event $event
 * @property \App\Model\Entity\StandardChest $standard_chest
 */
class EventChest extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'event_id' => true,
        'standard_chest_id' => true,
        'source' => true,
        'event' => true,
        'standard_chest' => true,
    ];
}
