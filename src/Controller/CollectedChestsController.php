<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\Controller\Controller;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;
use Cake\I18n\FrozenTime;

/**
 * CollectedChests Controller
 *
 * @property \App\Model\Table\CollectedChestsTable $CollectedChests
 */
class CollectedChestsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->CollectedChests->find();
        $collectedChests = $this->paginate($query);

        $this->set(compact('collectedChests'));
    }

    /**
     * View method
     *
     * @param string|null $id Collected Chest id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $collectedChest = $this->CollectedChests->get($id, contain: []);
        $this->set(compact('collectedChest'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $collectedChest = $this->CollectedChests->newEmptyEntity();
        if ($this->request->is('post')) {
            $collectedChest = $this->CollectedChests->patchEntity($collectedChest, $this->request->getData());
            if ($this->CollectedChests->save($collectedChest)) {
                $this->Flash->success(__('The collected chest has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The collected chest could not be saved. Please, try again.'));
        }
        $this->set(compact('collectedChest'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Collected Chest id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $collectedChest = $this->CollectedChests->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $collectedChest = $this->CollectedChests->patchEntity($collectedChest, $this->request->getData());
            if ($this->CollectedChests->save($collectedChest)) {
                $this->Flash->success(__('The collected chest has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The collected chest could not be saved. Please, try again.'));
        }
        $this->set(compact('collectedChest'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Collected Chest id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $collectedChest = $this->CollectedChests->get($id);
        if ($this->CollectedChests->delete($collectedChest)) {
            $this->Flash->success(__('The collected chest has been deleted.'));
        } else {
            $this->Flash->error(__('The collected chest could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
    public function score()
    {
        $configsTable = TableRegistry::getTableLocator()->get('Config');
        $collectedChestsTable = TableRegistry::getTableLocator()->get('CollectedChests');
        $standardChestsTable = TableRegistry::getTableLocator()->get('StandardChests');

        // Buscar o dia de referência
        $referenceDayConfig = $configsTable->find()
            ->where(['param' => 'reference_day'])
            ->first();

        if (!$referenceDayConfig || empty($referenceDayConfig->value)) {
            return 'Parâmetro reference_day não encontrado ou vazio.';
        }

        $referenceDay = new FrozenTime($referenceDayConfig->value);

        // Buscar a duração do ciclo em dias
        $everyHowManyDaysConfig = $configsTable->find()
            ->where(['param' => 'every_how_many_days'])
            ->first();

        if (!$everyHowManyDaysConfig || !is_numeric($everyHowManyDaysConfig->value)) {
            return 'Parâmetro every_how_many_days não encontrado ou inválido.';
        }
        $cycleDuration = (int) $everyHowManyDaysConfig->value;

        // Buscar a pontuação mínima
        $minimumChestScore = $configsTable->find()
            ->where(['param' => 'minimum_chest_score'])
            ->first();
        if (!$minimumChestScore || !is_numeric($minimumChestScore->value)) {
            return 'Parâmetro minimum_chest_score não encontrado ou inválido.';
        }
        $minimumChestScore = (int) $minimumChestScore->value;

        // Determinar o ciclo a ser calculado
        $selectedCycleOffset = $this->request->getQuery('cycle', 0); // 0 para o ciclo atual

        $today = FrozenTime::now();
        $daysSinceReference = $referenceDay->diffInDays($today);
        $currentCycleOffset = (int) floor($daysSinceReference / $cycleDuration);
        $targetCycleOffset = $currentCycleOffset - $selectedCycleOffset;
        $cycleStart = $referenceDay->addDays($targetCycleOffset * $cycleDuration);
        $cycleEnd = $cycleStart->addDays($cycleDuration)->sub(new \DateInterval('PT1S'));

        // Buscar os baús coletados no ciclo selecionado
        $collectedChestsData = $collectedChestsTable->find()
            ->select(['player', 'source', 'count' => 'COUNT(*)'])
            ->where([
                'collected_at >=' => $cycleStart,
                'collected_at <=' => $cycleEnd,
            ])
            ->group(['player', 'source'])
            ->toArray();

        // Buscar a pontuação de cada tipo de baú

        $chestScoresResult = $standardChestsTable->find()
            ->select(['source', 'score'])
            ->toArray();
        
        $chestScores = [];
        foreach ($chestScoresResult as $row) {
            $chestScores[$row->source] = $row;
        }

        $playerChestCounts = [];
        $playerFinalScores = [];

        // Processar os dados dos baús coletados
        foreach ($collectedChestsData as $data) {
            $player = $data->player;
            $source = $data->source;
            $count = $data->count;

            if (!isset($playerChestCounts[$player])) {
                $playerChestCounts[$player] = [];
                $playerFinalScores[$player] = 0;
            }
            $playerChestCounts[$player][$source] = $count;

            if (isset($chestScores[$source])) {
                $playerFinalScores[$player] += $chestScores[$source]->score * $count;
            }
        }

        // Calcular a soma total de baús por jogador
        $playerTotalChests = [];
        foreach ($playerChestCounts as $player => $counts) {
            $playerTotalChests[$player] = array_sum($counts);
        }

        // Gerar as opções para o select box
        $cycleOptions = [];
        for ($i = 0; $i <= 3; $i++) {
            $offset = $currentCycleOffset - $i;
            $start = $referenceDay->addDays($offset * $cycleDuration)->format('Y-m-d');
            $end = $referenceDay->addDays(($offset + 1) * $cycleDuration)->format('Y-m-d');
            $cycleOptions[$i] = ($i === 0 ? 'Ciclo Atual' : 'Ciclo Anterior ' . $i) . " ($start - $end)";
        }

        // Formatar as datas do ciclo atual para exibição
        $currentCycleFormatted = [
            'start' => $cycleStart->format('Y-m-d H:i:s'),
            'end' => $cycleEnd->format('Y-m-d H:i:s'),
        ];

        // Passar os dados para a view
        $this->set(compact('playerChestCounts', 'playerTotalChests', 'playerFinalScores', 'cycleOptions', 'currentCycleFormatted', 'selectedCycleOffset', 'minimumChestScore'));

    }
}
