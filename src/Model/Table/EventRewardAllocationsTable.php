<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

/**
 * EventRewardAllocations Model: how much of one reward one player receives.
 *
 * @property \App\Model\Table\EventRewardsTable&\Cake\ORM\Association\BelongsTo $EventRewards
 * @property \App\Model\Table\EventStandingsTable&\Cake\ORM\Association\BelongsTo $EventStandings
 */
class EventRewardAllocationsTable extends Table
{
    /**
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('event_reward_allocations');
        $this->setPrimaryKey('id');

        $this->belongsTo('EventRewards', [
            'foreignKey' => 'event_reward_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('EventStandings', [
            'foreignKey' => 'event_standing_id',
            'joinType' => 'INNER',
        ]);
    }
}
