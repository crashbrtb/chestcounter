<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EventStandings Model
 *
 * @property \App\Model\Table\EventsTable&\Cake\ORM\Association\BelongsTo $Events
 * @method \App\Model\Entity\EventStanding newEmptyEntity()
 * @method \App\Model\Entity\EventStanding newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EventStanding get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\EventStanding patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventStanding|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 */
class EventStandingsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('event_standings');
        $this->setDisplayField('player');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp', [
            'events' => ['Model.beforeSave' => ['created' => 'new']],
        ]);

        $this->belongsTo('Events', [
            'foreignKey' => 'event_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('player')
            ->maxLength('player', 50)
            ->requirePresence('player', 'create')
            ->notEmptyString('player');

        $validator
            ->integer('position')
            ->requirePresence('position', 'create');

        return $validator;
    }
}
