<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * BankApprovalLogs Model
 *
 * @property \App\Model\Table\BankTransactionsTable&\Cake\ORM\Association\BelongsTo $BankTransactions
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $AdminUsers
 *
 * @method \App\Model\Entity\BankApprovalLog newEmptyEntity()
 * @method \App\Model\Entity\BankApprovalLog newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\BankApprovalLog> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\BankApprovalLog get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\BankApprovalLog findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\BankApprovalLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\BankApprovalLog> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\BankApprovalLog|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\BankApprovalLog saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\BankApprovalLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\BankApprovalLog>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\BankApprovalLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\BankApprovalLog> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\BankApprovalLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\BankApprovalLog>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\BankApprovalLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\BankApprovalLog> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class BankApprovalLogsTable extends Table
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

        $this->setTable('bank_approval_logs');
        $this->setDisplayField('action');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('BankTransactions', [
            'foreignKey' => 'bank_transaction_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('AdminUsers', [
            'foreignKey' => 'admin_user_id',
            'className' => 'Users',
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
            ->integer('bank_transaction_id')
            ->notEmptyString('bank_transaction_id');

        $validator
            ->integer('admin_user_id')
            ->notEmptyString('admin_user_id');

        $validator
            ->scalar('action')
            ->maxLength('action', 20)
            ->requirePresence('action', 'create')
            ->notEmptyString('action');

        $validator
            ->scalar('original_values')
            ->maxLength('original_values', 4294967295)
            ->allowEmptyString('original_values');

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
        $rules->add($rules->existsIn(['bank_transaction_id'], 'BankTransactions'), ['errorField' => 'bank_transaction_id']);
        $rules->add($rules->existsIn(['admin_user_id'], 'AdminUsers'), ['errorField' => 'admin_user_id']);

        return $rules;
    }
}
