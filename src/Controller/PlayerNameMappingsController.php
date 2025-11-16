<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * PlayerNameMappings Controller
 *
 * @property \App\Model\Table\PlayerNameMappingsTable $PlayerNameMappings
 */
class PlayerNameMappingsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */

    public function initialize(): void
    {
        parent::initialize();
        // Carrega o helper de layout do CakeLte
        $this->viewBuilder()->setLayout('CakeLte/layout/default');
    }
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);

    }
    public function index()
    {
        $query = $this->PlayerNameMappings->find();
        $playerNameMappings = $this->paginate($query);

        $this->set(compact('playerNameMappings'));
    }

    /**
     * View method
     *
     * @param string|null $id Player Name Mapping id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $playerNameMapping = $this->PlayerNameMappings->get($id, contain: []);
        $this->set(compact('playerNameMapping'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $playerNameMapping = $this->PlayerNameMappings->newEmptyEntity();
        if ($this->request->is('post')) {
            $playerNameMapping = $this->PlayerNameMappings->patchEntity($playerNameMapping, $this->request->getData());
            if ($this->PlayerNameMappings->save($playerNameMapping)) {
                $this->Flash->success(__('The player name mapping has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The player name mapping could not be saved. Please, try again.'));
        }
        $this->set(compact('playerNameMapping'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Player Name Mapping id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $playerNameMapping = $this->PlayerNameMappings->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $playerNameMapping = $this->PlayerNameMappings->patchEntity($playerNameMapping, $this->request->getData());
            if ($this->PlayerNameMappings->save($playerNameMapping)) {
                $this->Flash->success(__('The player name mapping has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The player name mapping could not be saved. Please, try again.'));
        }
        $this->set(compact('playerNameMapping'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Player Name Mapping id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $playerNameMapping = $this->PlayerNameMappings->get($id);
        if ($this->PlayerNameMappings->delete($playerNameMapping)) {
            $this->Flash->success(__('The player name mapping has been deleted.'));
        } else {
            $this->Flash->error(__('The player name mapping could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
