<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\JobRun;
use App\Service\HealthCheckService;
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @uses \App\Controller\Api\HealthController
 */
class HealthControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use LocatorAwareTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Config',
        'app.Users',
        'app.JobRuns',
        'app.MonitoredJobs',
    ];

    /**
     * @return array<string, mixed>
     */
    private function body(): array
    {
        return json_decode((string)$this->_response->getBody(), true) ?? [];
    }

    public function testWithoutAKeyConfiguredItSaysSo(): void
    {
        $this->get('/api/v1/health.json');

        $this->assertResponseCode(503);
    }

    public function testWrongOrMissingKeyIsRefused(): void
    {
        (new HealthCheckService())->regenerateKey();

        $this->get('/api/v1/health.json');
        $this->assertResponseCode(401);

        $this->configRequest(['headers' => ['X-Monitor-Key' => 'ccm_wrong']]);
        $this->get('/api/v1/health.json');
        $this->assertResponseCode(401);
    }

    public function testReportIsReturnedWithTheKeyEvenWhenJobsAreDown(): void
    {
        $key = (new HealthCheckService())->regenerateKey();
        $runs = $this->fetchTable('JobRuns');
        $runs->saveOrFail($runs->newEntity([
            'job' => 'collector',
            'status' => JobRun::STATUS_SUCCESS,
            'started_at' => DateTime::now()->subMinutes(20),
            'finished_at' => DateTime::now()->subMinutes(10),
            'summary' => ['collected' => 0],
        ]));

        $this->configRequest(['headers' => ['X-Monitor-Key' => $key]]);
        $this->get('/api/v1/health.json');

        $this->assertResponseCode(200);
        $body = $this->body();
        // The backup and the maintenance never ran.
        $this->assertFalse($body['ok']);
        $states = array_column($body['jobs'], 'state', 'job');
        $this->assertSame('ok', $states['collector']);
        $this->assertSame('never', $states['database_backup']);
    }

    public function testKeyCanComeInTheQueryString(): void
    {
        $key = (new HealthCheckService())->regenerateKey();

        $this->get('/api/v1/health.json?key=' . $key);

        $this->assertResponseCode(200);
    }
}
