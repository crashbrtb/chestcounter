<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * BankTransaction Entity
 *
 * @property int $id
 * @property int $member_id
 * @property int $user_id
 * @property string $type
 * @property string $amount
 * @property string $fee
 * @property string $final_amount
 * @property string|null $description
 * @property string $status
 * @property int|null $destination_member_id
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Member $member
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Member $destination_member
 * @property \App\Model\Entity\BankApprovalLog[] $bank_approval_logs
 */
class BankTransaction extends Entity
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
        'member_id' => true,
        'user_id' => true,
        'type' => true,
        'amount' => true,
        'fee' => true,
        'final_amount' => true,
        'description' => true,
        'status' => true,
        'destination_member_id' => true,
        'created' => true,
        'modified' => true,
        'member' => true,
        'user' => true,
        'destination_member' => true,
        'bank_approval_logs' => true,
    ];
}
