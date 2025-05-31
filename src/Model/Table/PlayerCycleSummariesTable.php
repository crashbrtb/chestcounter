<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * PlayerCycleSummaries Model
 *
 * @method \App\Model\Entity\PlayerCycleSummary newEmptyEntity() 
 * @method \App\Model\Entity\PlayerCycleSummary newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\PlayerCycleSummary[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PlayerCycleSummary get($primaryKey, $options = [])
 * @method \App\Model\Entity\PlayerCycleSummary findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\PlayerCycleSummary patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\PlayerCycleSummary[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PlayerCycleSummary|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PlayerCycleSummary saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PlayerCycleSummary[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\PlayerCycleSummary[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\PlayerCycleSummary[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\PlayerCycleSummary[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PlayerCycleSummariesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('player_cycle_summaries');
        $this->setDisplayField('id');
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
            ->scalar('player_name')
            ->maxLength('player_name', 255)
            ->requirePresence('player_name', 'create')
            ->notEmptyString('player_name');

        $validator
            ->date('cycle_start_date')
            ->requirePresence('cycle_start_date', 'create')
            ->notEmptyDate('cycle_start_date');

        $validator
            ->date('cycle_end_date')
            ->requirePresence('cycle_end_date', 'create')
            ->notEmptyDate('cycle_end_date');

        $validator
            ->integer('total_chests')
            ->notEmptyString('total_chests');

        $validator
            ->integer('total_score')
            ->notEmptyString('total_score');

        $validator
            ->boolean('goal_achieved')
            ->notEmptyString('goal_achieved');

        $validator
            ->boolean('fine_due')
            ->notEmptyString('fine_due');

        $validator
            ->boolean('fine_paid')
            ->notEmptyString('fine_paid');

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
        // Adiciona uma regra para garantir que player_name e cycle_start_date sejam únicos juntos
        $rules->add($rules->isUnique(['player_name', 'cycle_start_date']), [
            'errorField' => 'player_name',
            'message' => 'This player already has a summary for this cycle start date.'
        ]);

        return $rules;
    }
} 