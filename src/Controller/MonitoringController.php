<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\JobRunsTable;
use App\Service\HealthCheckService;
use Cake\Routing\Router;

/**
 * Whether the jobs the site depends on are running: the chest collector, the
 * maintenance, the database backup and the tournament uploads.
 *
 * The page shows what `/api/v1/health` tells the external monitor, lets an
 * administrator decide how long each job may stay silent, and holds the key the
 * monitor sends.
 */
class MonitoringController extends AppController
{
    /**
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->requireAdmin();

        $health = new HealthCheckService();
        $monitoredJobs = $this->fetchTable('MonitoredJobs');

        if ($this->request->is(['post', 'put'])) {
            if ($this->request->getData('section') === 'key') {
                $health->regenerateKey();
                $this->Flash->success(
                    __('A new monitoring key was created. Update it in the monitor: the old one no longer works.')
                );

                return $this->redirect(['action' => 'index']);
            }

            $failed = false;
            foreach ((array)$this->request->getData('jobs') as $id => $data) {
                $job = $monitoredJobs->find()->where(['id' => (int)$id])->first();
                if ($job === null || !is_array($data)) {
                    continue;
                }
                $job = $monitoredJobs->patchEntity($job, [
                    'max_silence_minutes' => $data['max_silence_minutes'] ?? $job->max_silence_minutes,
                    'enabled' => !empty($data['enabled']),
                ]);
                if (!$monitoredJobs->save($job)) {
                    $failed = true;
                    $this->Flash->error(__('"{0}" was not saved: {1}', $job->label, implode(' ', array_map(
                        fn (array $errors): string => implode(' ', $errors),
                        $job->getErrors()
                    ))));
                }
            }
            if (!$failed) {
                $this->Flash->success(__('Monitoring limits saved.'));
            }

            return $this->redirect(['action' => 'index']);
        }

        $jobFilter = (string)$this->request->getQuery('job', '');
        $runsQuery = $this->fetchTable('JobRuns')->find()
            ->orderBy(['started_at' => 'DESC', 'id' => 'DESC'])
            ->limit(50);
        if ($jobFilter !== '') {
            $runsQuery->where(['job' => $jobFilter]);
        }

        $this->set([
            'report' => $health->report(),
            'monitoredJobs' => $monitoredJobs->find()->orderBy(['id' => 'ASC'])->all(),
            'runs' => $runsQuery->all(),
            'jobFilter' => $jobFilter,
            'monitorKey' => $health->key(),
            'healthUrl' => Router::url('/api/v1/health.json', true),
            'retentionDays' => JobRunsTable::RETENTION_DAYS,
        ]);
    }
}
