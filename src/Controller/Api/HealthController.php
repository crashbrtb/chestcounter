<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\HealthCheckService;
use Cake\Controller\Controller;
use Cake\Http\Response;

/**
 * Health of the monitored jobs, for an external monitor (a Google Apps Script).
 *
 * The monitor sends the read-only key from Admin > Monitoring in the
 * `X-Monitor-Key` header, or as `?key=` where headers are awkward. The API
 * tokens are not used on purpose: they belong to an administrator and can
 * publish rankings, which a script that only reads has no business holding.
 *
 * With the right key the answer is always 200, whatever the jobs look like, so
 * the monitor can tell "a job is down" (`ok: false`) from "the site is down"
 * (no answer, or any other status).
 */
class HealthController extends Controller
{
    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Authentication.Authentication');
        $this->Authentication->allowUnauthenticated(['index']);
    }

    /**
     * @return \Cake\Http\Response
     */
    public function index(): Response
    {
        $this->request->allowMethod(['get']);

        $health = new HealthCheckService();
        if ($health->key() === null) {
            return $this->json(['error' => __('No monitoring key is set. Create one under Admin > Monitoring.')], 503);
        }

        $sent = trim($this->request->getHeaderLine('X-Monitor-Key'));
        if ($sent === '') {
            $sent = (string)$this->request->getQuery('key', '');
        }
        if (!$health->isValidKey($sent)) {
            return $this->json(['error' => __('Send the monitoring key in the X-Monitor-Key header.')], 401);
        }

        return $this->json($health->report());
    }

    /**
     * @param array<string, mixed> $data Body.
     * @param int $status HTTP status.
     * @return \Cake\Http\Response
     */
    private function json(array $data, int $status = 200): Response
    {
        return $this->response
            ->withStatus($status)
            ->withType('application/json')
            ->withHeader('Cache-Control', 'no-store')
            ->withStringBody((string)json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
