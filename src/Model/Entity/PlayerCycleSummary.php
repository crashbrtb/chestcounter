<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * PlayerCycleSummary Entity
 *
 * @property int $id
 * @property string $player_name
 * @property \Cake\I18n\FrozenDate $cycle_start_date
 * @property \Cake\I18n\FrozenDate $cycle_end_date
 * @property int $total_chests
 * @property int $total_score
 * @property bool $goal_achieved
 * @property bool $fine_due
 * @property bool $fine_paid
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class PlayerCycleSummary extends Entity
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
        'player_name' => true,
        'cycle_start_date' => true,
        'cycle_end_date' => true,
        'total_chests' => true,
        'total_score' => true,
        'goal_achieved' => true,
        'fine_due' => true,
        'fine_paid' => true,
        'created' => true,
        'modified' => true,
    ];
} 