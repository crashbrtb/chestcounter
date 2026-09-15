<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MonitoredJobs Model: what the health check expects, and how patiently.
 *
 * @method \App\Model\Entity\MonitoredJob newEmptyEntity()
 * @method \App\Model\Entity\MonitoredJob get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 */
class MonitoredJobsTable extends Table
{
    public const MIN_SILENCE_MINUTES = 5;

    /** Two weeks. */
    public const MAX_SILENCE_MINUTES = 20160;

    /**
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('monitored_jobs');
        $this->setDisplayField('label');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('label')
            ->maxLength('label', 80)
            ->notEmptyString('label');

        $validator
            ->integer('max_silence_minutes')
            ->range(
                'max_silence_minutes',
                [self::MIN_SILENCE_MINUTES, self::MAX_SILENCE_MINUTES],
                __('Between {0} and {1} minutes.', self::MIN_SILENCE_MINUTES, self::MAX_SILENCE_MINUTES)
            );

        $validator->boolean('enabled');

        return $validator;
    }
}
