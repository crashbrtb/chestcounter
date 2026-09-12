<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * Event Entity
 *
 * @property int $id
 * @property int $event_number
 * @property string $name
 * @property string|null $description
 * @property string $criteria
 * @property string $custom_metric
 * @property \Cake\I18n\DateTime $starts_at
 * @property \Cake\I18n\DateTime $ends_at
 * @property string $prize
 * @property string $contact_player
 * @property string|null $banner_mime
 * @property resource|string|null $banner_image
 * @property string $status
 * @property \Cake\I18n\DateTime|null $finalized_at
 * @property int|null $created_by
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property array<\App\Model\Entity\EventChest> $event_chests
 * @property array<\App\Model\Entity\EventStanding> $event_standings
 * @property string $state
 * @property bool $is_running
 * @property bool $has_custom_banner
 */
class Event extends Entity
{
    public const CRITERIA_CHEST_COUNT = 'chest_count';
    public const CRITERIA_CHEST_SCORE = 'chest_score';
    public const CRITERIA_EPIC_MONSTER = 'epic_monster';
    public const CRITERIA_CUSTOM_CHESTS = 'custom_chests';

    public const METRIC_SCORE = 'score';
    public const METRIC_COUNT = 'count';

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Derived lifecycle states. Only `cancelled` is stored; the rest follow from
     * the window, so an event starts and ends without anybody having to run a job.
     */
    public const STATE_SCHEDULED = 'scheduled';
    public const STATE_RUNNING = 'running';
    public const STATE_FINISHED = 'finished';
    public const STATE_CANCELLED = 'cancelled';

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * `event_number` is deliberately absent: it is assigned by the table, never
     * by the form, so two administrators cannot pick the same one.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'name' => true,
        'description' => true,
        'criteria' => true,
        'custom_metric' => true,
        'starts_at' => true,
        'ends_at' => true,
        'prize' => true,
        'contact_player' => true,
        'banner_mime' => true,
        'banner_image' => true,
        'status' => true,
        'finalized_at' => true,
        'created_by' => true,
        'event_chests' => true,
    ];

    /**
     * The blob never belongs in a serialized entity: it is served by its own
     * action and would otherwise be dragged into every JSON response.
     *
     * @var list<string>
     */
    protected array $_hidden = ['banner_image'];

    /**
     * @var list<string>
     */
    protected array $_virtual = ['state', 'is_running', 'has_custom_banner'];

    /**
     * Human-readable labels for each criteria value.
     *
     * @return array<string, string>
     */
    public static function criteriaOptions(): array
    {
        return [
            self::CRITERIA_CHEST_COUNT => __('Number of chests collected'),
            self::CRITERIA_CHEST_SCORE => __('Chest score'),
            self::CRITERIA_EPIC_MONSTER => __('Number of epic monster chests'),
            self::CRITERIA_CUSTOM_CHESTS => __('Custom chests'),
        ];
    }

    /**
     * Short explanation of what each criteria counts, shown under the selector.
     *
     * @return array<string, string>
     */
    public static function criteriaHints(): array
    {
        return [
            self::CRITERIA_CHEST_COUNT => __('Every chest collected inside the event window counts as one point.'),
            self::CRITERIA_CHEST_SCORE => __('Chests are worth the score configured for their type.'),
            self::CRITERIA_EPIC_MONSTER => __('Only chests from epic monsters count, one point each.'),
            self::CRITERIA_CUSTOM_CHESTS => __('Only the chest types you pick below count.'),
        ];
    }

    /**
     * Label for this event's criteria.
     *
     * @return string
     */
    public function criteriaLabel(): string
    {
        return self::criteriaOptions()[$this->criteria] ?? (string)$this->criteria;
    }

    /**
     * What the "points" column of the standings actually measures, for the
     * dashboard headings.
     *
     * @return string
     */
    public function pointsLabel(): string
    {
        return match (true) {
            $this->criteria === self::CRITERIA_CHEST_COUNT => __('Chests'),
            $this->criteria === self::CRITERIA_EPIC_MONSTER => __('Epic Chests'),
            $this->criteria === self::CRITERIA_CUSTOM_CHESTS
                && $this->custom_metric === self::METRIC_COUNT => __('Chests'),
            default => __('Total Score'),
        };
    }

    /**
     * Lifecycle state derived from the stored status and the event window.
     *
     * @return string
     */
    protected function _getState(): string
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return self::STATE_CANCELLED;
        }

        $now = DateTime::now();
        if ($this->starts_at !== null && $now->lessThan($this->starts_at)) {
            return self::STATE_SCHEDULED;
        }

        if ($this->ends_at !== null && $now->greaterThan($this->ends_at)) {
            return self::STATE_FINISHED;
        }

        return self::STATE_RUNNING;
    }

    /**
     * @return bool
     */
    protected function _getIsRunning(): bool
    {
        return $this->state === self::STATE_RUNNING;
    }

    /**
     * @return bool
     */
    protected function _getHasCustomBanner(): bool
    {
        return !empty($this->banner_image);
    }
}
