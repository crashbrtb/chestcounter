<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\Controller\Controller;
use Cake\ORM\TableRegistry;
use Cake\I18n\FrozenTime;
Use Cake\I18n\DateTime;

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

    public function update()
    {
        $collectedChestsTable = TableRegistry::getTableLocator()->get('CollectedChests');

        // Calcula a data de 7 dias atrás
        $sevenDaysAgo = FrozenTime::now()->subDays(7);

        // Busca jogadores únicos na tabela collected_chests nos últimos 7 dias
        $collectedPlayers = $collectedChestsTable->find()
            ->select(['player'])
            ->where(['collected_at >=' => $sevenDaysAgo])
            ->distinct(['player'])
            ->toArray();

        $collectedPlayerNames = [];
        foreach ($collectedPlayers as $player) {
            $collectedPlayerNames[] = $player->player;
        }
        

        // Atualiza ou cria membros com base em collected_chests
        foreach ($collectedPlayerNames as $playerName) {
            $member = $this->Members->find()->where(['player' => $playerName])->first();

            if ($member) {
                // Jogador já existe na tabela members
                if ($member->active != 1) {
                    $member->active = 1;
                }
                $member->modified_at = FrozenTime::now();
                $this->Members->save($member);
            } else {
                // Jogador não existe na tabela members, cria um novo
                $newMember = $this->Members->newEmptyEntity();
                $newMember->player = $playerName;
                $newMember->active = 1;
                $newMember->modified_at = FrozenTime::now();
                var_dump($newMember);
                $this->Members->save($newMember);
            }
        }

        // Desativa membros que não estão em collected_chests nos últimos 7 dias
        $allMembers = $this->Members->find()->toArray();
        foreach ($allMembers as $member) {
            if (!in_array($member->player, $collectedPlayerNames)) {
                $member->active = 0;
                $member->modified_at = FrozenTime::now();
                $this->Members->save($member);
            }
        }

        $this->Flash->success(__('Tabela Members atualizada com sucesso.'));
        return $this->redirect(['action' => 'index']); // Ou para onde você quiser redirecionar
    }
}
