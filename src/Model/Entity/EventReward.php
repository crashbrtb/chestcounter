<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * One thing an imported event hands out, and how it is split.
 *
 * @property int $id
 * @property int $event_id
 * @property string $item_name
 * @property int $quantity
 * @property string $rule
 * @property int $min_points
 * @property string $remainder
 * @property int $sort
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 */
class EventReward extends Entity
{
    public const RULE_EQUAL = 'equal';
    public const RULE_PROPORTIONAL = 'proportional';

    public const REMAINDER_TOP_RANKED = 'top_ranked';
    public const REMAINDER_KEEP = 'keep';

    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'item_name' => true,
        'quantity' => true,
        'rule' => true,
        'min_points' => true,
        'remainder' => true,
        'sort' => true,
    ];

    /**
     * @return array<string, string>
     */
    public static function ruleOptions(): array
    {
        return [
            self::RULE_PROPORTIONAL => __('Proportional to the points'),
            self::RULE_EQUAL => __('Equal parts'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function remainderOptions(): array
    {
        return [
            self::REMAINDER_TOP_RANKED => __('Give the leftover to the best placed'),
            self::REMAINDER_KEEP => __('Keep the leftover with the clan'),
        ];
    }

    /**
     * Short text for lists: "500 x Artifact pieces (proportional)".
     *
     * @return string
     */
    public function summary(): string
    {
        return __('{0} x {1} ({2})', number_format((int)$this->quantity, 0, ',', '.'), $this->item_name, mb_strtolower(
            self::ruleOptions()[$this->rule] ?? (string)$this->rule
        ));
    }
}
