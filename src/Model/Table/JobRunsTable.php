<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\JobRun;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * JobRuns Model: the heartbeat every monitored job writes.
 *
 * @method \App\Model\Entity\JobRun newEmptyEntity()
 * @method \App\Model\Entity\JobRun get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 */
class JobRunsTable extends Table
{
    /**
     * How long run history is kept.
     */
    public const RETENTION_DAYS = 60;

    /**
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('job_runs');
        $this->setDisplayField('job');
        $this->setPrimaryKey('id');

        // The collector writes this column as JSON text straight into MySQL.
        $this->getSchema()->setColumnType('summary', 'json');

        $this->addBehavior('Timestamp', [
            'events' => ['Model.beforeSave' => ['created' => 'new']],
        ]);
    }

    /**
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('job')
            ->maxLength('job', 40)
            ->requirePresence('job', 'create')
            ->notEmptyString('job');

        $validator
            ->inList('status', JobRun::STATUSES)
            ->requirePresence('status', 'create');

        $validator
            ->scalar('host')
            ->maxLength('host', 100)
            ->allowEmptyString('host');

        return $validator;
    }

    /**
     * The newest run of a job, whatever its outcome.
     *
     * @param string $job Job key.
     * @return \App\Model\Entity\JobRun|null
     */
    public function latest(string $job): ?JobRun
    {
        /** @var \App\Model\Entity\JobRun|null $run */
        $run = $this->find()
            ->where(['job' => $job])
            ->orderBy(['started_at' => 'DESC', 'id' => 'DESC'])
            ->first();

        return $run;
    }

    /**
     * The newest run of a job that proves it is alive.
     *
     * @param string $job Job key.
     * @return \App\Model\Entity\JobRun|null
     */
    public function latestGood(string $job): ?JobRun
    {
        /** @var \App\Model\Entity\JobRun|null $run */
        $run = $this->find()
            ->where(['job' => $job, 'status IN' => JobRun::GOOD_STATUSES])
            ->orderBy(['finished_at' => 'DESC', 'id' => 'DESC'])
            ->first();

        return $run;
    }

    /**
     * Delete history older than the retention, keeping the last good run of
     * each job so a job that has been down for months still shows when it last
     * worked.
     *
     * @param int $days Days to keep.
     * @return int Rows deleted.
     */
    public function purgeOlderThan(int $days = self::RETENTION_DAYS): int
    {
        $keep = [];
        foreach ($this->find()->select(['job'])->distinct(['job'])->all() as $row) {
            $good = $this->latestGood((string)$row->job);
            if ($good !== null) {
                $keep[] = $good->id;
            }
        }

        $conditions = ['started_at <' => DateTime::now()->subDays($days)];
        if ($keep) {
            $conditions['id NOT IN'] = $keep;
        }

        return $this->deleteAll($conditions);
    }
}
