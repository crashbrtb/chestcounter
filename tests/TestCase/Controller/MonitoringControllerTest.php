<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Entity\JobRun;
use App\Service\HealthCheckService;
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @uses \App\Controller\MonitoringController
 */
class MonitoringControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use LocatorAwareTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Config',
        'app.Users',
        'app.Roles',
        // User 1 holds role 1, which is what requireAdmin() looks for.
        'app.RolesUsers',
        'app.JobRuns',
        'app.MonitoredJobs',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->enableCsrfToken();
        $this->enableRetainFlashMessages();
    }

    /**
     * @param int $id User id.
     * @return void
     */
    private function signIn(int $id): void
    {
        $this->session([
            'Auth' => [
                'id' => $id,
                'username' => 'tester',
                'email' => 'tester@example.com',
                'created' => new DateTime('2026-01-01 00:00:00'),
            ],
        ]);
    }

    public function testPageIsClosedToNonAdministrators(): void
    {
        $this->signIn(2);

        $this->get('/monitoring');

        $this->assertResponseCode(403);
    }

    public function testPageShowsJobsRunsAndTheScript(): void
    {
        $key = (new HealthCheckService())->regenerateKey();
        $runs = $this->fetchTable('JobRuns');
        $runs->saveOrFail($runs->newEntity([
            'job' => 'collector',
            'status' => JobRun::STATUS_PARTIAL,
            'host' => 'CLAN-PC',
            'started_at' => DateTime::now()->subMinutes(15),
            'finished_at' => DateTime::now()->subMinutes(5),
            'summary' => [
                'collected' => 12,
                'failures' => [['account' => 'Main', 'profile' => 'City 2', 'reason' => 'gift menu did not open']],
            ],
        ]));
        $this->signIn(1);

        $this->get('/monitoring');

        $this->assertResponseOk();
        $this->assertResponseContains('Chest collector');
        $this->assertResponseContains('CLAN-PC');
        $this->assertResponseContains('Main / City 2 — gift menu did not open');
        $this->assertResponseContains($key);
        $this->assertResponseContains('/api/v1/health.json');
        $this->assertResponseContains('function checkHealth()');
    }

    public function testLimitsAreSaved(): void
    {
        $this->signIn(1);

        $this->post('/monitoring', [
            'section' => 'jobs',
            'jobs' => [
                1 => ['max_silence_minutes' => '240', 'enabled' => '1'],
                // Unchecked switch: not sent.
                3 => ['max_silence_minutes' => '1500'],
            ],
        ]);

        $this->assertRedirect(['controller' => 'Monitoring', 'action' => 'index']);
        $jobs = $this->fetchTable('MonitoredJobs');
        $this->assertSame(240, $jobs->get(1)->max_silence_minutes);
        $this->assertFalse($jobs->get(3)->enabled);
    }

    public function testLimitOutOfRangeIsRefused(): void
    {
        $this->signIn(1);

        $this->post('/monitoring', [
            'section' => 'jobs',
            'jobs' => [1 => ['max_silence_minutes' => '1', 'enabled' => '1']],
        ]);

        $this->assertFlashElement('flash/error');
        $this->assertSame(180, $this->fetchTable('MonitoredJobs')->get(1)->max_silence_minutes);
    }

    public function testKeyCanBeReplaced(): void
    {
        $old = (new HealthCheckService())->regenerateKey();
        $this->signIn(1);

        $this->post('/monitoring', ['section' => 'key']);

        $this->assertRedirect(['controller' => 'Monitoring', 'action' => 'index']);
        $this->assertFalse((new HealthCheckService())->isValidKey($old));
    }
}
