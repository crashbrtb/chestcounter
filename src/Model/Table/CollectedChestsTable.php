<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CollectedChests Model
 *
 * @method \App\Model\Entity\CollectedChest newEmptyEntity()
 * @method \App\Model\Entity\CollectedChest newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\CollectedChest> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CollectedChest get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\CollectedChest findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\CollectedChest patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\CollectedChest> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CollectedChest|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\CollectedChest saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\CollectedChest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CollectedChest>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CollectedChest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CollectedChest> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CollectedChest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CollectedChest>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CollectedChest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CollectedChest> deleteManyOrFail(iterable $entities, array $options = [])
 */
class CollectedChestsTable extends Table
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

        $this->setTable('collected_chests');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');
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
            ->scalar('name')
            ->maxLength('name', 50)
            ->notEmptyString('name');

        $validator
            ->scalar('player')
            ->maxLength('player', 50)
            ->notEmptyString('player');

        $validator
            ->scalar('source')
            ->maxLength('source', 50)
            ->notEmptyString('source');

        $validator
            ->notEmptyString('type');

        $validator
            ->dateTime('collected_at')
            ->notEmptyDateTime('collected_at');

        return $validator;
    }
}
