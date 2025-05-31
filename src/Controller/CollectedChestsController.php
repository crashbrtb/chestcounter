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
        for ($i = 0; $i <= 1; $i++) {
            $offset = $currentCycleOffset - $i;
            $start = $referenceDay->addDays($offset * $cycleDuration)->format('Y-m-d');
            $end = $referenceDay->addDays(($offset + 1) * $cycleDuration)->format('Y-m-d');
            $cycleOptions[$i] = ($i === 0 ?  __('Current Cycle') : __('Previous Cycle') ) . " ($start - $end)";
        }

        // Formatar as datas do ciclo atual para exibição
        $currentCycleFormatted = [
            'start' => $cycleStart->format('Y-m-d H:i:s'),
            'end' => $cycleEnd->format('Y-m-d H:i:s'),
        ];

        // Buscar a data/hora da linha mais recente da tabela CollectedChests
        $lastUpdate = $collectedChestsTable->find()
            ->order(['collected_at' => 'DESC'])
            ->first();

        // Desativa o sidebar especificamente para esta action
        $this->set('cakelte_theme', [
            'sidebar' => [
                'enable' => false
            ],
            'navbar' => [
                'enable' => true
            ]
        ]);

        // Passar os dados para a view
        $this->set(compact('playerChestCounts', 'playerTotalChests', 'playerFinalScores', 'cycleOptions', 'currentCycleFormatted', 'selectedCycleOffset', 'minimumChestScore', 'lastUpdate'));

    }

    public function mergePlayers()
    {
        $collectedChestsTable = $this->CollectedChests; // Ou TableRegistry::getTableLocator()->get('CollectedChests');
        $membersTable = TableRegistry::getTableLocator()->get('Members'); // Adicionar MembersTable

        $uniquePlayersQuery = $collectedChestsTable->find()
            ->select(['player'])
            ->distinct(['player'])
            ->order(['player' => 'ASC']);
        
        $playerList = $uniquePlayersQuery->all()->combine('player', 'player')->toArray();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $correctPlayer = $data['correct_player_name'] ?? null;
            $incorrectPlayer = $data['incorrect_player_name'] ?? null;

            if (empty($correctPlayer) || empty($incorrectPlayer)) {
                $this->Flash->error(__('Please select both the correct player name and the incorrect player name.'));
            } elseif ($correctPlayer === $incorrectPlayer) {
                $this->Flash->error(__('The correct and incorrect player names cannot be the same.'));
            } else {
                try {
                    $updatedRows = $collectedChestsTable->updateAll(
                        ['player' => $correctPlayer],
                        ['player' => $incorrectPlayer]
                    );

                    if ($updatedRows > 0) {
                        $this->Flash->success(__('Successfully merged player "{0}" into "{1}". {2} records were updated.', $incorrectPlayer, $correctPlayer, $updatedRows));
                        
                        // Excluir o jogador incorreto da tabela Members
                        $incorrectPlayerEntity = $membersTable->find()->where(['player' => $incorrectPlayer])->first();
                        if ($incorrectPlayerEntity) {
                            if ($membersTable->delete($incorrectPlayerEntity)) {
                                $this->Flash->success(__('Player "{0}" was successfully deleted from the members list.', $incorrectPlayer));
                            } else {
                                $this->Flash->error(__('Could not delete player "{0}" from the members list.', $incorrectPlayer));
                            }
                        } else {
                            // Opcional: Adicionar uma mensagem se o jogador incorreto não for encontrado na tabela Members
                            // $this->Flash->info(__('Player "{0}" not found in the members list, no deletion needed.', $incorrectPlayer));
                        }

                         // Atualizar a lista de jogadores após a mesclagem
                        $playerList = $collectedChestsTable->find()
                                            ->select(['player'])
                                            ->distinct(['player'])
                                            ->order(['player' => 'ASC'])
                                            ->all()
                                            ->combine('player', 'player')
                                            ->toArray();
                    } else {
                        $this->Flash->warning(__('No records found for player "{0}" to merge into "{1}". No changes were made.', $incorrectPlayer, $correctPlayer));
                    }
                } catch (\Exception $e) {
                    $this->Flash->error(__('An error occurred while merging players: {0}', $e->getMessage()));
                }
            }
        }

        $this->set(compact('playerList'));
        $this->set('title', __('Merge Player Names')); // Para o título da página
    }
}
