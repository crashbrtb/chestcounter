<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;

/**
 * Users Model
 *
 * @property \App\Model\Table\MembersTable&\Cake\ORM\Association\HasMany $Members
 * @property \App\Model\Table\RolesTable&\Cake\ORM\Association\BelongsToMany $Roles
 *
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\User> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\User findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\User> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
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

        $this->setTable('users');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Members', [
            'foreignKey' => 'user_id',
        ]);
        $this->belongsToMany('Roles', [
            'foreignKey' => 'user_id',
            'targetForeignKey' => 'role_id',
            'joinTable' => 'roles_users',
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
            ->scalar('name')
            ->maxLength('name', 60)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->allowEmptyString('password', null, 'update');

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
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);
        $rules->add($rules->isUnique(['google_id']), [
            'errorField' => 'google_id',
            'message' => 'This Google account is already linked to another user.',
        ]);

        return $rules;
    }

    public function findAuth(SelectQuery $query, array $options): SelectQuery
    {
        $query
            ->contain(['Roles'])
            ->where(['Users.active' => 1]);

        return $query;
    }

    /**
     * Find or create a user by Google OAuth payload.
     * If user exists with the same email, links the Google ID.
     * If user does not exist, creates an inactive user pending admin approval.
     *
     * @param array $googlePayload Decoded Google JWT payload with 'sub', 'email', 'name', 'picture'.
     * @return \App\Model\Entity\User|null The user entity, or null if user is inactive.
     */
    public function findOrCreateByGoogle(array $googlePayload): ?\App\Model\Entity\User
    {
        $googleId = $googlePayload['sub'];
        $email = $googlePayload['email'];
        $name = $googlePayload['name'] ?? $email;

        // First, try to find by google_id
        $user = $this->find()
            ->contain(['Roles'])
            ->where(['Users.google_id' => $googleId])
            ->first();

        if ($user) {
            return $user->active ? $user : null;
        }

        // Try to find by email and link Google ID
        $user = $this->find()
            ->contain(['Roles'])
            ->where(['Users.email' => $email])
            ->first();

        if ($user) {
            $user->google_id = $googleId;
            $this->save($user);
            return $user->active ? $user : null;
        }

        // Create new inactive user (pending admin approval)
        $newUser = $this->newEntity([
            'name' => $name,
            'email' => $email,
            'google_id' => $googleId,
            'active' => 0,
            'password' => null,
        ], ['validate' => 'googleSignup']);

        // Assign default 'user' role
        $rolesTable = TableRegistry::getTableLocator()->get('Roles');
        $userRole = $rolesTable->find()->where(['name' => 'user'])->first();
        if ($userRole) {
            $newUser->roles = [$userRole];
        }

        if ($this->save($newUser)) {
            return null; // User created but inactive, needs admin approval
        }

        return null;
    }

    /**
     * Validation rules for Google signup (no password required).
     */
    public function validationGoogleSignup(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 60)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validator
            ->allowEmptyString('password');

        return $validator;
    }
}
