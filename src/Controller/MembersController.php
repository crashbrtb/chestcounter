<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\ORM\TableRegistry;
use Cake\I18n\FrozenTime;

/**
 * Members Controller
 *
 * @property \App\Model\Table\MembersTable $Members
 */
class MembersController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Members->find();
        $members = $this->paginate($query);

        $this->set(compact('members'));
    }

    /**
     * View method
     *
     * @param string|null $id Member id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $member = $this->Members->get($id, contain: []);
        $this->set(compact('member'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $member = $this->Members->newEmptyEntity();
        if ($this->request->is('post')) {
            $member = $this->Members->patchEntity($member, $this->request->getData());
            if ($this->Members->save($member)) {
                $this->Flash->success(__('The member has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The member could not be saved. Please, try again.'));
        }
        $this->set(compact('member'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Member id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $member = $this->Members->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $member = $this->Members->patchEntity($member, $this->request->getData());
            if ($this->Members->save($member)) {
                $this->Flash->success(__('The member has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The member could not be saved. Please, try again.'));
        }
        $this->set(compact('member'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Member id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $member = $this->Members->get($id);
        if ($this->Members->delete($member)) {
            $this->Flash->success(__('The member has been deleted.'));
        } else {
            $this->Flash->error(__('The member could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Update members from collected chests
     * Sincroniza players da tabela collectedchests com a tabela members
     * e atualiza o status active baseado na última atividade (3 semanas)
     *
     * @return \Cake\Http\Response|null Redirects to index with flash message
     */
    public function updateFromCollectedChests()
    {
        $collectedChestsTable = TableRegistry::getTableLocator()->get('CollectedChests');
        
        // Buscar todos os players únicos da tabela collectedchests
        $allPlayers = $collectedChestsTable->find()
            ->select(['player'])
            ->distinct(['player'])
            ->toArray();

        // Debug: verificar quantos players foram encontrados
        $playersCount = count($allPlayers);
        
        // Debug: mostrar alguns nomes de players para verificar
        $samplePlayers = array_slice($allPlayers, 0, 3);
        $samplePlayerNames = [];
        foreach ($samplePlayers as $player) {
            $samplePlayerNames[] = $player->player;
        }
        
        $newMembersCount = 0;
        $updatedMembersCount = 0;
        $threeWeeksAgo = FrozenTime::now()->subWeeks(3);

        foreach ($allPlayers as $playerData) {
            $playerName = $playerData->player;
            
            // Buscar a última atividade deste player específico
            $lastActivity = $collectedChestsTable->find()
                ->select(['collected_at'])
                ->where(['player' => $playerName])
                ->order(['collected_at' => 'DESC'])
                ->first();
            
            if (!$lastActivity) {
                continue; // Pular se não encontrar atividade
            }
            
            $lastCollectedAt = $lastActivity->collected_at;
            
            // Verificar se o membro já existe
            $existingMember = $this->Members->find()
                ->where(['player' => $playerName])
                ->first();

            if ($existingMember) {
                // Membro existe - verificar se precisa atualizar o status active
                $isActive = $lastCollectedAt >= $threeWeeksAgo ? 1 : 0;
                
                if ($existingMember->active != $isActive) {
                    $existingMember->active = $isActive;
                    
                    if ($this->Members->save($existingMember)) {
                        $updatedMembersCount++;
                    }
                }
            } else {
                // Membro não existe - criar novo registro
                $newMember = $this->Members->newEmptyEntity();
                $isActive = $lastCollectedAt >= $threeWeeksAgo ? 1 : 0;
                
                $newMember = $this->Members->patchEntity($newMember, [
                    'player' => $playerName,
                    'active' => $isActive,
                    'power' => 0,
                    'guards' => 0,
                    'specialists' => 0,
                    'monsters' => 0,
                    'engineers' => 0
                ]);

                if ($this->Members->save($newMember)) {
                    $newMembersCount++;
                } else {
                    // Debug: mostrar erros de validação se houver
                    $errors = $newMember->getErrors();
                    if (!empty($errors)) {
                        $this->Flash->error(__('Error saving member {0}: {1}', $playerName, json_encode($errors)));
                    }
                }
            }
        }

        // Mensagem de feedback com informações de debug
        $message = __('Update completed. Found {0} players (samples: {1}). {2} new members added, {3} members updated.', 
                     $playersCount,
                     implode(', ', $samplePlayerNames),
                     $newMembersCount, 
                     $updatedMembersCount);
        
        $this->Flash->success($message);
        
        return $this->redirect(['action' => 'index']);
    }
}
