<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\Controller\Controller;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;

/**
 * StandardChests Controller
 *
 * @property \App\Model\Table\StandardChestsTable $StandardChests
 */
class StandardChestsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
    
        $this->Authentication->allowUnauthenticated(['weights']);
    }
    public function index()
    {
        $query = $this->StandardChests->find();
        $standardChests = $this->paginate($query);

        $this->set(compact('standardChests'));
    }

    public function weights()
    {
        // Define os campos pelos quais a paginação pode ordenar
        // Use os nomes reais das colunas no banco de dados.
        // Se 'Chests' no template se refere a 'source' na tabela:
        $this->paginate = [
            'sortableFields' => [
                'source', // Para $this->Paginator->sort('Chests') ou $this->Paginator->sort('source')
                'score'   // Para $this->Paginator->sort('Score') ou $this->Paginator->sort('score')
            ]
        ];

        $query = $this->StandardChests->find();
        $standardChests = $this->paginate($query);

        $this->set(compact('standardChests'));
    }

    /**
     * View method
     *
     * @param string|null $id Standard Chest id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $standardChest = $this->StandardChests->get($id, contain: []);
        $this->set(compact('standardChest'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $standardChest = $this->StandardChests->newEmptyEntity();
        if ($this->request->is('post')) {
            $standardChest = $this->StandardChests->patchEntity($standardChest, $this->request->getData());
            if ($this->StandardChests->save($standardChest)) {
                $this->Flash->success(__('The standard chest has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The standard chest could not be saved. Please, try again.'));
        }
        $this->set(compact('standardChest'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Standard Chest id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $standardChest = $this->StandardChests->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $standardChest = $this->StandardChests->patchEntity($standardChest, $this->request->getData());
            if ($this->StandardChests->save($standardChest)) {
                $this->Flash->success(__('The standard chest has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The standard chest could not be saved. Please, try again.'));
        }
        $this->set(compact('standardChest'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Standard Chest id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $standardChest = $this->StandardChests->get($id);
        if ($this->StandardChests->delete($standardChest)) {
            $this->Flash->success(__('The standard chest has been deleted.'));
        } else {
            $this->Flash->error(__('The standard chest could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

   
}
