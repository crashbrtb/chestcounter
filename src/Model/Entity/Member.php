<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Member Entity
 *
 * @property int $id
 * @property string $player
 * @property int $power
 * @property int $guards
 * @property int $specialists
 * @property int $monsters
 * @property int $engineers
 * @property int $active
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime $modified_at
 */
class Member extends Entity
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
        'player' => true,
        'power' => true,
        'guards' => true,
        'specialists' => true,
        'monsters' => true,
        'engineers' => true,
        'active' => true,
        'created_at' => true,
        'modified_at' => true,
    ];
}
