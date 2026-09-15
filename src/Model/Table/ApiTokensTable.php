<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\ApiToken;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ApiTokens Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @method \App\Model\Entity\ApiToken newEmptyEntity()
 * @method \App\Model\Entity\ApiToken get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 */
class ApiTokensTable extends Table
{
    /**
     * Visible start of every token, so one pasted in the wrong place is recognisable.
     */
    public const TOKEN_PREFIX = 'cct_';

    /**
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('api_tokens');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 60)
            ->requirePresence('name', 'create')
            ->notEmptyString('name', __('Give the token a name, such as the computer it will be used on.'));

        $validator
            ->dateTime('expires_at')
            ->allowEmptyDateTime('expires_at');

        return $validator;
    }

    /**
     * Create a token for a user.
     *
     * @param int $userId Owner.
     * @param string $name Label chosen by the administrator.
     * @return array{0: \App\Model\Entity\ApiToken, 1: string|null} The entity and, when saved, the plain token.
     */
    public function issue(int $userId, string $name): array
    {
        $plain = self::TOKEN_PREFIX . bin2hex(random_bytes(24));

        $token = $this->newEntity(['name' => trim($name)]);
        $token->set('user_id', $userId);
        $token->set('token_hash', self::hash($plain));
        $token->set('prefix', substr($plain, 0, strlen(self::TOKEN_PREFIX) + 6));

        return $this->save($token) ? [$token, $plain] : [$token, null];
    }

    /**
     * The active token matching what a client sent, or null.
     *
     * @param string $plain Token from the Authorization header.
     * @return \App\Model\Entity\ApiToken|null
     */
    public function findActiveByPlainToken(string $plain): ?ApiToken
    {
        $plain = trim($plain);
        if ($plain === '' || !str_starts_with($plain, self::TOKEN_PREFIX)) {
            return null;
        }

        /** @var \App\Model\Entity\ApiToken|null $token */
        $token = $this->find()
            ->where([
                'ApiTokens.token_hash' => self::hash($plain),
                'ApiTokens.revoked_at IS' => null,
                'OR' => [
                    'ApiTokens.expires_at IS' => null,
                    'ApiTokens.expires_at >' => DateTime::now(),
                ],
            ])
            ->contain(['Users'])
            ->first();

        return $token;
    }

    /**
     * Note when and from where a token was last used.
     *
     * @param \App\Model\Entity\ApiToken $token The token.
     * @param string|null $ip Client address.
     * @return void
     */
    public function recordUse(ApiToken $token, ?string $ip): void
    {
        $this->updateAll(
            ['last_used_at' => DateTime::now(), 'last_used_ip' => $ip !== null ? substr($ip, 0, 45) : null],
            ['id' => $token->id]
        );
    }

    /**
     * @param string $plain Plain token.
     * @return string
     */
    public static function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }
}
