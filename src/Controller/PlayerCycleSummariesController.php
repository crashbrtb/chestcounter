<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\ORM\TableRegistry;
use Cake\I18n\FrozenTime;

/**
 * PlayerCycleSummaries Controller
 *
 * @property \App\Model\Table\PlayerCycleSummariesTable $PlayerCycleSummaries
 */
class PlayerCycleSummariesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->PlayerCycleSummaries->find()
            ->order(['cycle_start_date' => 'DESC', 'player_name' => 'ASC']);
        $playerCycleSummaries = $this->paginate($query);

        $this->set(compact('playerCycleSummaries'));
    }

    /**
     * View method
     *
     * @param string|null $id Player Cycle Summary id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $playerCycleSummary = $this->PlayerCycleSummaries->get($id, contain: []);
        $this->set(compact('playerCycleSummary'));
    }

    /**
     * Mark Fine as Paid method
     *
     * @param string|null $id Player Cycle Summary id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function markFinePaid($id = null)
    {
        $this->request->allowMethod(['post', 'put']);
        $playerCycleSummary = $this->PlayerCycleSummaries->get($id);
        if ($playerCycleSummary->fine_due && !$playerCycleSummary->fine_paid) {
            $playerCycleSummary->fine_paid = true;
            if ($this->PlayerCycleSummaries->save($playerCycleSummary)) {
                $this->Flash->success(__('Fine marked as paid for {0} in cycle starting {1}.', $playerCycleSummary->player_name, $playerCycleSummary->cycle_start_date->format('Y-m-d')));
            } else {
                $this->Flash->error(__('The fine could not be marked as paid. Please, try again.'));
            }
        } else {
            $this->Flash->warning(__('No fine to mark as paid or it has already been paid.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Process Cycle Summaries method
     * 
     * This method will be called, possibly by a cron job or manually, 
     * to process the scores for a completed cycle.
     */
    public function processCycleSummaries()
    {
        $this->request->allowMethod(['post', 'get']); // Permitir GET para teste manual
        $configsTable = TableRegistry::getTableLocator()->get('Config');
        $collectedChestsTable = TableRegistry::getTableLocator()->get('CollectedChests');
        $standardChestsTable = TableRegistry::getTableLocator()->get('StandardChests');
        $playerCycleSummariesTable = $this->PlayerCycleSummaries;

        // --- Lógica para determinar o ciclo a ser processado (ex: o último ciclo concluído) ---
        $referenceDayConfig = $configsTable->find()->where(['param' => 'reference_day'])->first();
        $everyHowManyDaysConfig = $configsTable->find()->where(['param' => 'every_how_many_days'])->first();
        $minimumChestScoreConfig = $configsTable->find()->where(['param' => 'minimum_chest_score'])->first();

        if (!($referenceDayConfig && $everyHowManyDaysConfig && $minimumChestScoreConfig && 
              !empty($referenceDayConfig->value) && is_numeric($everyHowManyDaysConfig->value) && is_numeric($minimumChestScoreConfig->value))) {
            $this->Flash->error(__('Configuration parameters for cycle processing are missing or invalid.'));
            return $this->redirect(['action' => 'index']);
        }

        $referenceDay = new FrozenTime($referenceDayConfig->value);
        $cycleDuration = (int) $everyHowManyDaysConfig->value;
        $minimumRequiredScore = (int) $minimumChestScoreConfig->value;

        $today = FrozenTime::now();
        $daysSinceReference = $referenceDay->diffInDays($today);
        $currentCycleOffsetFromReference = (int) floor($daysSinceReference / $cycleDuration);
        
        // Obter o offset do ciclo a ser processado da URL, default é 0 (ciclo que acabou de fechar)
        $requestedCycleOffset = $this->request->getQuery('cycle_offset');
        if ($requestedCycleOffset === null || !is_numeric($requestedCycleOffset)) {
            $targetCycleToProcessOffset = $currentCycleOffsetFromReference - 1; // Ciclo anterior ao atual
        } else {
            $targetCycleToProcessOffset = $currentCycleOffsetFromReference - 1 - (int)$requestedCycleOffset;
        }

        // Evitar processar ciclos futuros ou muito antigos (além da referência)
        if ($targetCycleToProcessOffset < 0) {
             $this->Flash->warning(__('Invalid cycle offset. Cannot process future cycles or cycles before the reference start.'));
             return $this->redirect(['action' => 'index']);
        }

        $cycleStart = $referenceDay->addDays($targetCycleToProcessOffset * $cycleDuration);
        $cycleEnd = $cycleStart->addDays($cycleDuration)->sub(new \DateInterval('PT1S'));

        // Verificar se este ciclo já foi processado para algum jogador
        $existingSummaryCheck = $playerCycleSummariesTable->find()
            ->where(['cycle_start_date' => $cycleStart->format('Y-m-d')])
            ->count();
        
        if ($existingSummaryCheck > 0 && !$this->request->getQuery('force_reprocess')) {
            $this->Flash->info(__('Cycle from {0} to {1} has already been processed. Use ?force_reprocess=1 to re-process.', $cycleStart->format('Y-m-d'), $cycleEnd->format('Y-m-d')));
            return $this->redirect(['action' => 'index']);
        }
        
        // Se forçar reprocessamento, limpar registros existentes para este ciclo
        if ($this->request->getQuery('force_reprocess')) {
            $playerCycleSummariesTable->deleteAll(['cycle_start_date' => $cycleStart->format('Y-m-d')]);
            $this->Flash->info(__('Cleared existing summaries for cycle {0} due to force_reprocess.', $cycleStart->format('Y-m-d')));
        }

        // --- Lógica copiada e adaptada de CollectedChestsController::score() ---
        $collectedChestsData = $collectedChestsTable->find()
            ->select(['player', 'source', 'count' => 'COUNT(*)'])
            ->where([
                'collected_at >=' => $cycleStart,
                'collected_at <=' => $cycleEnd,
            ])
            ->group(['player', 'source'])
            ->toArray();

        $chestScoresResult = $standardChestsTable->find()
            ->select(['source', 'score'])
            ->toArray();
        
        $chestScores = [];
        foreach ($chestScoresResult as $row) {
            $chestScores[$row->source] = $row->score;
        }

        $playerCycleData = [];

        foreach ($collectedChestsData as $data) {
            $player = $data->player;
            $source = $data->source;
            $count = $data->count;

            if (!isset($playerCycleData[$player])) {
                $playerCycleData[$player] = [
                    'total_chests' => 0,
                    'total_score' => 0,
                ];
            }
            $playerCycleData[$player]['total_chests'] += $count;
            if (isset($chestScores[$source])) {
                $playerCycleData[$player]['total_score'] += $chestScores[$source] * $count;
            }
        }

        $processedCount = 0;
        $errorCount = 0;

        foreach ($playerCycleData as $playerName => $data) {
            $goalAchieved = $data['total_score'] >= $minimumRequiredScore;
            $fineDue = !$goalAchieved;

            $summary = $playerCycleSummariesTable->newEntity([
                'player_name' => $playerName,
                'cycle_start_date' => $cycleStart->format('Y-m-d'),
                'cycle_end_date' => $cycleEnd->format('Y-m-d'),
                'total_chests' => $data['total_chests'],
                'total_score' => $data['total_score'],
                'goal_achieved' => $goalAchieved,
                'fine_due' => $fineDue,
                'fine_paid' => false, // Multa nunca é paga na criação
            ]);

            if ($playerCycleSummariesTable->save($summary)) {
                $processedCount++;
            } else {
                $errorCount++;
                // Log errors if necessary
                // Log::error('Failed to save summary for player ' . $playerName . ' for cycle ' . $cycleStart->format('Y-m-d'));
            }
        }

        if ($processedCount > 0) {
            $this->Flash->success(__('{0} player cycle summaries processed successfully for cycle {1} - {2}.', $processedCount, $cycleStart->format('Y-m-d'), $cycleEnd->format('Y-m-d')));
        }
        if ($errorCount > 0) {
            $this->Flash->error(__('{0} errors occurred while processing player cycle summaries.', $errorCount));
        }
        if ($processedCount === 0 && $errorCount === 0) {
            $this->Flash->info(__('No player data found to process for cycle {0} - {1}.', $cycleStart->format('Y-m-d'), $cycleEnd->format('Y-m-d')));
        }

        return $this->redirect(['action' => 'index']);
    }
} 