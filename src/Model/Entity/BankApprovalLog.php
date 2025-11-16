<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * BankApprovalLog Entity
 *
 * @property int $id
 * @property int $bank_transaction_id
 * @property int $admin_user_id
 * @property string $action
 * @property string|null $original_values
 * @property \Cake\I18n\DateTime|null $created
 *
 * @property \App\Model\Entity\BankTransaction $bank_transaction
 * @property \App\Model\Entity\User $admin_user
 */
class BankApprovalLog extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'bank_transaction_id' => true,
        'admin_user_id' => true,
        'action' => true,
        'original_values' => true,
        'created' => true,
        'bank_transaction' => true,
        'admin_user' => true,
    ];
}
