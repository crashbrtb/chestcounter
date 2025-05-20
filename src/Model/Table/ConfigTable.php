<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Config Model
 *
 * @method \App\Model\Entity\Config newEmptyEntity()
 * @method \App\Model\Entity\Config newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Config> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Config get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Config findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Config patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Config> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Config|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Config saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Config>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Config>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Config>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Config> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Config>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Config>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Config>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Config> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ConfigTable extends Table
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

        $this->setTable('config');
        $this->setDisplayField('param');
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
            ->scalar('param')
            ->maxLength('param', 45)
            ->requirePresence('param', 'create')
            ->notEmptyString('param');

        $validator
            ->scalar('value')
            ->maxLength('value', 45)
            ->requirePresence('value', 'create')
            ->notEmptyString('value');

        $validator
            ->scalar('description')
            ->maxLength('description', 512)
            ->requirePresence('description', 'create')
            ->notEmptyString('description');

        return $validator;
    }
}
