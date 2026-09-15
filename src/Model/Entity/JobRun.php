<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * One run of a monitored job: the collector, the backup, the maintenance.
 *
 * A run is written as `running` when it starts and closed when it ends, so a
 * process that dies half way leaves a row that never closes rather than no
 * trace at all.
 *
 * @property int $id
 * @property string $job
 * @property string $status
 * @property string|null $host
 * @property \Cake\I18n\DateTime $started_at
 * @property \Cake\I18n\DateTime|null $finished_at
 * @property array<string, mixed>|null $summary
 * @property \Cake\I18n\DateTime|null $created
 */
class JobRun extends Entity
{
    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCESS = 'success';
    /** The job ran, but some of its parts failed (a profile, a maintenance step). */
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Statuses that prove the job is alive.
     *
     * @var list<string>
     */
    public const GOOD_STATUSES = [self::STATUS_SUCCESS, self::STATUS_PARTIAL];

    /**
     * @var list<string>
     */
    public const STATUSES = [
        self::STATUS_RUNNING,
        self::STATUS_SUCCESS,
        self::STATUS_PARTIAL,
        self::STATUS_FAILED,
        self::STATUS_CANCELLED,
    ];

    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'job' => true,
        'status' => true,
        'host' => true,
        'started_at' => true,
        'finished_at' => true,
        'summary' => true,
    ];
}
