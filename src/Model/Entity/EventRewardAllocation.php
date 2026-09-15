<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property int $event_reward_id
 * @property int $event_standing_id
 * @property int $amount
 */
class EventRewardAllocation extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'event_reward_id' => true,
        'event_standing_id' => true,
        'amount' => true,
    ];
}
