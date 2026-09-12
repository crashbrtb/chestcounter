<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EventChests Model
 *
 * @property \App\Model\Table\EventsTable&\Cake\ORM\Association\BelongsTo $Events
 * @property \App\Model\Table\StandardChestsTable&\Cake\ORM\Association\BelongsTo $StandardChests
 * @method \App\Model\Entity\EventChest newEmptyEntity()
 * @method \App\Model\Entity\EventChest newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EventChest get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\EventChest patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventChest|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 */
class EventChestsTable extends Table
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

        $this->setTable('event_chests');
        $this->setDisplayField('source');
        $this->setPrimaryKey('id');

        $this->belongsTo('Events', [
            'foreignKey' => 'event_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('StandardChests', [
            'foreignKey' => 'standard_chest_id',
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
            ->integer('standard_chest_id')
            ->requirePresence('standard_chest_id', 'create')
            ->notEmptyString('standard_chest_id');

        $validator
            ->scalar('source')
            ->maxLength('source', 50)
            ->requirePresence('source', 'create')
            ->notEmptyString('source');

        return $validator;
    }
}
