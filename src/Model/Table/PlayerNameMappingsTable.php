<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * PlayerNameMappings Model
 *
 * @method \App\Model\Entity\PlayerNameMapping newEmptyEntity()
 * @method \App\Model\Entity\PlayerNameMapping newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\PlayerNameMapping> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PlayerNameMapping get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\PlayerNameMapping findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\PlayerNameMapping patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\PlayerNameMapping> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PlayerNameMapping|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\PlayerNameMapping saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\PlayerNameMapping>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PlayerNameMapping>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\PlayerNameMapping>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PlayerNameMapping> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\PlayerNameMapping>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PlayerNameMapping>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\PlayerNameMapping>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PlayerNameMapping> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PlayerNameMappingsTable extends Table
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

        $this->setTable('player_name_mappings');
        $this->setDisplayField('ocr_text');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
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
            ->scalar('ocr_text')
            ->maxLength('ocr_text', 50)
            ->requirePresence('ocr_text', 'create')
            ->notEmptyString('ocr_text')
            ->add('ocr_text', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('correct_name')
            ->maxLength('correct_name', 50)
            ->requirePresence('correct_name', 'create')
            ->notEmptyString('correct_name');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['ocr_text']), ['errorField' => 'ocr_text']);

        return $rules;
    }
}
