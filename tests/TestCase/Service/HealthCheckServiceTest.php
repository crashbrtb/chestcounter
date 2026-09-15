<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Model\Entity\JobRun;
use App\Service\HealthCheckService;
use App\Service\JobRunRecorder;
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;

/**
 * @uses \App\Service\HealthCheckService
 * @uses \App\Service\JobRunRecorder
 */
class HealthCheckServiceTest extends TestCase
{
    use LocatorAwareTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Config',
        'app.JobRuns',
        'app.MonitoredJobs',
    ];

    protected DateTime $now;

    protected function setUp(): void
    {
        parent::setUp();
        $this->now = new DateTime('2026-09-14 12:00:00');
    }

    /**
     * @param string $job Job key.
     * @param string $status Status.
     * @param int $minutesAgo When it started, before $now.
     * @param bool $closed Whether it has finished (one minute after starting).
     * @param array<string, mixed>|null $summary Summary.
     * @return void
     */
    private function addRun(string $job, string $status, int $minutesAgo, bool $closed = true, ?array $summary = null): void
    {
        $runs = $this->fetchTable('JobRuns');
        $started = $this->now->subMinutes($minutesAgo);
        $runs->saveOrFail($runs->newEntity([
            'job' => $job,
            'status' => $status,
            'host' => 'test',
            'started_at' => $started,
            'finished_at' => $closed ? $started->addMinutes(1) : null,
            'summary' => $summary,
        ]));
    }

    /**
     * @param string $job Job key.
     * @return array<string, mixed>
     */
    private function job(string $job): array
    {
        foreach ((new HealthCheckService())->report($this->now)['jobs'] as $entry) {
            if ($entry['job'] === $job) {
                return $entry;
            }
        }
        $this->fail("Job {$job} not in the report");
    }

    public function testJobThatNeverRanIsDown(): void
    {
        $report = (new HealthCheckService())->report($this->now);

        $this->assertFalse($report['ok']);
        $this->assertSame(HealthCheckService::STATE_NEVER, $this->job('collector')['state']);
    }

    public function testRunWithoutChestsIsAlive(): void
    {
        $this->addRun('collector', JobRun::STATUS_SUCCESS, 30, true, ['collected' => 0]);

        $entry = $this->job('collector');
        $this->assertSame(HealthCheckService::STATE_OK, $entry['state']);
        $this->assertTrue($entry['ok']);
        $this->assertSame(29, $entry['minutes_since_success']);
        $this->assertSame(['collected' => 0], $entry['last_run']['summary']);
    }

    public function testGoodRunOlderThanTheLimitIsStale(): void
    {
        $this->addRun('collector', JobRun::STATUS_SUCCESS, 300);
        $this->addRun('collector', JobRun::STATUS_FAILED, 60);

        $this->assertSame(HealthCheckService::STATE_STALE, $this->job('collector')['state']);
    }

    public function testRunThatNeverClosedIsStuck(): void
    {
        $this->addRun('collector', JobRun::STATUS_SUCCESS, 400);
        $this->addRun('collector', JobRun::STATUS_RUNNING, 200, false);

        $this->assertSame(HealthCheckService::STATE_STUCK, $this->job('collector')['state']);
    }

    public function testRunInProgressAfterAGoodOneIsFine(): void
    {
        $this->addRun('collector', JobRun::STATUS_SUCCESS, 90);
        $this->addRun('collector', JobRun::STATUS_RUNNING, 5, false);

        $this->assertSame(HealthCheckService::STATE_OK, $this->job('collector')['state']);
    }

    public function testPartialOrFailedLastRunIsAWarningButNotDown(): void
    {
        foreach (['collector', 'daily_maintenance', 'database_backup'] as $job) {
            $this->addRun($job, JobRun::STATUS_SUCCESS, 120);
        }
        $this->addRun('collector', JobRun::STATUS_PARTIAL, 10);
        $this->addRun('daily_maintenance', JobRun::STATUS_FAILED, 10);

        $report = (new HealthCheckService())->report($this->now);

        $this->assertTrue($report['ok']);
        $this->assertSame(2, $report['warnings']);
        $this->assertSame(HealthCheckService::STATE_WARNING, $this->job('collector')['state']);
        $this->assertSame(HealthCheckService::STATE_WARNING, $this->job('daily_maintenance')['state']);
        $this->assertSame(HealthCheckService::STATE_OK, $this->job('database_backup')['state']);
    }

    public function testDisabledJobNeverFailsTheReport(): void
    {
        foreach (['collector', 'daily_maintenance', 'database_backup'] as $job) {
            $this->addRun($job, JobRun::STATUS_SUCCESS, 5);
        }

        $report = (new HealthCheckService())->report($this->now);

        $this->assertTrue($report['ok']);
        $this->assertSame(HealthCheckService::STATE_DISABLED, $this->job('tournament_import')['state']);
    }

    public function testRecorderOpensAndClosesARun(): void
    {
        $recorder = new JobRunRecorder();
        $run = $recorder->start(JobRunRecorder::JOB_DATABASE_BACKUP, 'server-1');
        $this->assertNotNull($run);
        $this->assertSame(JobRun::STATUS_RUNNING, $this->fetchTable('JobRuns')->get($run->id)->status);

        $recorder->finish($run, JobRun::STATUS_SUCCESS, ['file' => 'dump.sql.gz']);

        $stored = $this->fetchTable('JobRuns')->get($run->id);
        $this->assertSame(JobRun::STATUS_SUCCESS, $stored->status);
        $this->assertSame('server-1', $stored->host);
        $this->assertNotNull($stored->finished_at);
        $this->assertSame(['file' => 'dump.sql.gz'], $stored->summary);
    }

    public function testPurgeKeepsTheLastGoodRunOfEachJob(): void
    {
        $runs = $this->fetchTable('JobRuns');
        $old = DateTime::now()->subDays(90);
        foreach ([JobRun::STATUS_SUCCESS, JobRun::STATUS_FAILED] as $i => $status) {
            $runs->saveOrFail($runs->newEntity([
                'job' => 'database_backup',
                'status' => $status,
                'started_at' => $old->addMinutes($i),
                'finished_at' => $old->addMinutes($i + 1),
            ]));
        }
        $runs->saveOrFail($runs->newEntity([
            'job' => 'database_backup',
            'status' => JobRun::STATUS_SUCCESS,
            'started_at' => $old->subDays(1),
            'finished_at' => $old->subDays(1),
        ]));

        $this->assertSame(2, $runs->purgeOlderThan(60));
        $left = $runs->find()->all()->toList();
        $this->assertCount(1, $left);
        $this->assertSame(JobRun::STATUS_SUCCESS, $left[0]->status);
    }

    public function testKeyIsCheckedAndCanBeReplaced(): void
    {
        $health = new HealthCheckService();
        $this->assertNull($health->key());
        $this->assertFalse($health->isValidKey('anything'));

        $first = $health->regenerateKey();
        $this->assertTrue($health->isValidKey($first));
        $this->assertFalse($health->isValidKey('ccm_wrong'));

        $second = $health->regenerateKey();
        $this->assertNotSame($first, $second);
        $this->assertFalse($health->isValidKey($first));
        $this->assertTrue($health->isValidKey($second));
    }
}
