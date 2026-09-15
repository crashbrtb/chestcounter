<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * A personal token the EventUploader desktop tool signs its requests with.
 *
 * Only the sha256 of the token is stored. The token itself is shown to the
 * administrator once, when it is created.
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $token_hash
 * @property string $prefix
 * @property \Cake\I18n\DateTime|null $last_used_at
 * @property string|null $last_used_ip
 * @property \Cake\I18n\DateTime|null $expires_at
 * @property \Cake\I18n\DateTime|null $revoked_at
 * @property \Cake\I18n\DateTime|null $created
 * @property \App\Model\Entity\User|null $user
 * @property bool $is_active
 */
class ApiToken extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'name' => true,
        'expires_at' => true,
    ];

    /**
     * @var list<string>
     */
    protected array $_hidden = ['token_hash'];

    /**
     * @return bool
     */
    protected function _getIsActive(): bool
    {
        if ($this->revoked_at !== null) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->greaterThan(DateTime::now());
    }
}
