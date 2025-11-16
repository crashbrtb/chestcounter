<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * BankTransactions Model
 *
 * @property \App\Model\Table\MembersTable&\Cake\ORM\Association\BelongsTo $Members
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\MembersTable&\Cake\ORM\Association\BelongsTo $DestinationMembers
 * @property \App\Model\Table\BankApprovalLogsTable&\Cake\ORM\Association\HasMany $BankApprovalLogs
 *
 * @method \App\Model\Entity\BankTransaction newEmptyEntity()
 * @method \App\Model\Entity\BankTransaction newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\BankTransaction> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\BankTransaction get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\BankTransaction findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\BankTransaction patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\BankTransaction> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\BankTransaction|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\BankTransaction saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\BankTransaction>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\BankTransaction>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\BankTransaction>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\BankTransaction> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\BankTransaction>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\BankTransaction>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\BankTransaction>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\BankTransaction> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class BankTransactionsTable extends Table
{
    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAWAL = 'withdrawal';
    public const TYPE_TRANSFER = 'transfer';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELED = 'canceled';

    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('bank_transactions');
        $this->setDisplayField('type');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Members', [
            'foreignKey' => 'member_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('DestinationMembers', [
            'foreignKey' => 'destination_member_id',
            'className' => 'Members',
        ]);
        $this->hasMany('BankApprovalLogs', [
            'foreignKey' => 'bank_transaction_id',
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
            ->integer('member_id')
            ->notEmptyString('member_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('type')
            ->maxLength('type', 20)
            ->requirePresence('type', 'create')
            ->notEmptyString('type');

        $validator
            ->integer('amount')
            ->requirePresence('amount', 'create')
            ->notEmptyString('amount');

        $validator
            ->integer('fee')
            ->requirePresence('fee', 'create')
            ->notEmptyString('fee');

        $validator
            ->integer('final_amount')
            ->requirePresence('final_amount', 'create')
            ->notEmptyString('final_amount');

        $validator
            ->scalar('description')
            ->maxLength('description', 512)
            ->allowEmptyString('description');

        $validator
            ->scalar('status')
            ->maxLength('status', 20)
            ->notEmptyString('status');

        $validator
            ->integer('destination_member_id')
            ->allowEmptyString('destination_member_id');

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
        $rules->add($rules->existsIn(['member_id'], 'Members'), ['errorField' => 'member_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['destination_member_id'], 'DestinationMembers'), ['errorField' => 'destination_member_id']);

        return $rules;
    }
}
