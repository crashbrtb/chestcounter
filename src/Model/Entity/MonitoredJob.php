<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * A job the health check expects to run, and how long it may stay silent.
 *
 * @property int $id
 * @property string $job
 * @property string $label
 * @property int $max_silence_minutes
 * @property bool $enabled
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 */
class MonitoredJob extends Entity
{
    /**
     * The job key is fixed: it is what the job itself writes in `job_runs`.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'label' => true,
        'max_silence_minutes' => true,
        'enabled' => true,
    ];
}
