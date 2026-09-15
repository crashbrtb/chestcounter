<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\JobRun;
use App\Model\Entity\MonitoredJob;
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Says, for every monitored job, whether it is alive.
 *
 * Alive means a good run (success or partial) within the job's silence limit.
 * That is deliberately about the process and not the data: a collector that
 * ran and found no chests is alive, and a collector that has not run is not,
 * however recent the newest chest happens to be.
 *
 * Job states:
 * - `ok`        a good run inside the limit, and the newest run went well.
 * - `warning`   alive, but the newest run failed, was partial or was cancelled.
 * - `stale`     no good run inside the limit.
 * - `stuck`     no good run inside the limit, and the newest run never closed.
 * - `never`     the job has never run.
 * - `disabled`  not watched; never makes the report fail.
 *
 * Only stale, stuck and never make the overall `ok` false.
 */
class HealthCheckService
{
    use LocatorAwareTrait;

    public const CONFIG_KEY = 'health_check_key';

    public const STATE_OK = 'ok';
    public const STATE_WARNING = 'warning';
    public const STATE_STALE = 'stale';
    public const STATE_STUCK = 'stuck';
    public const STATE_NEVER = 'never';
    public const STATE_DISABLED = 'disabled';

    /**
     * States that mean the job is down.
     *
     * @var list<string>
     */
    public const DOWN_STATES = [self::STATE_STALE, self::STATE_STUCK, self::STATE_NEVER];

    /**
     * The state of every monitored job.
     *
     * @param \Cake\I18n\DateTime|null $now Reference time, for tests.
     * @return array{ok: bool, warnings: int, checked_at: string, jobs: list<array<string, mixed>>}
     */
    public function report(?DateTime $now = null): array
    {
        $now ??= DateTime::now();

        $jobs = [];
        $ok = true;
        $warnings = 0;

        /** @var iterable<\App\Model\Entity\MonitoredJob> $monitored */
        $monitored = $this->fetchTable('MonitoredJobs')->find()->orderBy(['id' => 'ASC'])->all();
        foreach ($monitored as $job) {
            $entry = $this->check($job, $now);
            if (in_array($entry['state'], self::DOWN_STATES, true)) {
                $ok = false;
            } elseif ($entry['state'] === self::STATE_WARNING) {
                $warnings++;
            }
            $jobs[] = $entry;
        }

        return [
            'ok' => $ok,
            'warnings' => $warnings,
            'checked_at' => $now->toIso8601String(),
            'jobs' => $jobs,
        ];
    }

    /**
     * @param \App\Model\Entity\MonitoredJob $job The job.
     * @param \Cake\I18n\DateTime $now Reference time.
     * @return array<string, mixed>
     */
    public function check(MonitoredJob $job, DateTime $now): array
    {
        /** @var \App\Model\Table\JobRunsTable $runs */
        $runs = $this->fetchTable('JobRuns');
        $latest = $runs->latest($job->job);
        $good = $runs->latestGood($job->job);

        $goodAt = $good?->finished_at ?? $good?->started_at;
        $minutesSince = $goodAt !== null
            ? max(0, intdiv($now->getTimestamp() - $goodAt->getTimestamp(), 60))
            : null;
        $limit = (int)$job->max_silence_minutes;
        $troubled = [JobRun::STATUS_FAILED, JobRun::STATUS_PARTIAL, JobRun::STATUS_CANCELLED];

        if (!$job->enabled) {
            $state = self::STATE_DISABLED;
        } elseif ($latest === null) {
            $state = self::STATE_NEVER;
        } elseif ($minutesSince === null || $minutesSince > $limit) {
            $state = $latest->status === JobRun::STATUS_RUNNING ? self::STATE_STUCK : self::STATE_STALE;
        } elseif (in_array($latest->status, $troubled, true)) {
            $state = self::STATE_WARNING;
        } else {
            $state = self::STATE_OK;
        }

        return [
            'job' => $job->job,
            'label' => $job->label,
            'state' => $state,
            'ok' => !in_array($state, self::DOWN_STATES, true),
            'enabled' => (bool)$job->enabled,
            'max_silence_minutes' => $limit,
            'last_success_at' => $goodAt?->toIso8601String(),
            'minutes_since_success' => $minutesSince,
            'last_run' => $latest === null ? null : $this->describe($latest),
        ];
    }

    /**
     * @param \App\Model\Entity\JobRun $run The run.
     * @return array<string, mixed>
     */
    public function describe(JobRun $run): array
    {
        return [
            'status' => $run->status,
            'host' => $run->host,
            'started_at' => $run->started_at->toIso8601String(),
            'finished_at' => $run->finished_at?->toIso8601String(),
            'summary' => $run->summary,
        ];
    }

    /**
     * The key the external monitor has to send, or null when none is set.
     *
     * @return string|null
     */
    public function key(): ?string
    {
        $row = $this->fetchTable('Config')->find()->where(['param' => self::CONFIG_KEY])->first();
        $value = $row !== null ? trim((string)$row->value) : '';

        return $value !== '' ? $value : null;
    }

    /**
     * Whether a key a client sent is the right one.
     *
     * @param string|null $sent What the client sent.
     * @return bool
     */
    public function isValidKey(?string $sent): bool
    {
        $key = $this->key();

        return $key !== null && $sent !== null && hash_equals($key, trim($sent));
    }

    /**
     * Replace the key, so a leaked one stops working.
     *
     * @return string The new key.
     */
    public function regenerateKey(): string
    {
        $key = 'ccm_' . bin2hex(random_bytes(20));
        $config = $this->fetchTable('Config');
        $row = $config->find()->where(['param' => self::CONFIG_KEY])->first();
        if ($row === null) {
            $row = $config->newEntity([
                'param' => self::CONFIG_KEY,
                'value' => $key,
                'description' => 'Read-only key the external monitor sends to /api/v1/health. '
                    . 'Managed under Admin > Monitoring.',
            ]);
        }
        $row->set('value', $key);
        $config->saveOrFail($row);

        return $key;
    }
}
