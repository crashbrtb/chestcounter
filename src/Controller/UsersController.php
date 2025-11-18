<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\Event\EventInterface;
use Authentication\Authenticator\Result;
use Cake\Http\Exception\UnauthorizedException;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    
     public function beforeFilter(\Cake\Event\EventInterface $event)
     {
         parent::beforeFilter($event);
     
         $this->Authentication->allowUnauthenticated(['login']);
     }
    public function index()
    {
        $this->requireAdmin();
        $query = $this->Users->find()->contain(['Members']);
        $users = $this->paginate($query);

        $this->set(compact('users'));
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $this->requireAdmin();
        $user = $this->Users->get($id, contain: ['Roles', 'Members']);
        $this->set(compact('user'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->requireAdmin();
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();

            if (!empty($data['members']['_ids'])) {
                $selectedMembers = $this->Users->Members->find()
                    ->where(['Members.id IN' => $data['members']['_ids'], 'Members.user_id IS NOT' => null]);

                if ($selectedMembers->count() > 0) {
                    $this->Flash->error(__('One or more selected members are already associated with another user. Please deselect them.'));
                } else {
                    $userRole = $this->Users->Roles->findByName('user')->first();
                    if ($userRole) {
                        $data['roles']['_ids'] = [$userRole->id];
                    }
                    $user = $this->Users->patchEntity($user, $data);
                    if ($this->Users->save($user)) {
                        $this->Flash->success(__('The user has been saved.'));

                        return $this->redirect(['action' => 'index']);
                    }
                    $this->Flash->error(__('The user could not be saved. Please, try again.'));
                }
            } else {
                // Also handle the case where no members are selected, if that's allowed
                $userRole = $this->Users->Roles->findByName('user')->first();
                if ($userRole) {
                    $data['roles']['_ids'] = [$userRole->id];
                }
                $user = $this->Users->patchEntity($user, $data);
                if ($this->Users->save($user)) {
                    $this->Flash->success(__('The user has been saved.'));

                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error(__('The user could not be saved. Please, try again.'));
            }
        }
        $members = $this->Users->Members->find('list')->where(['user_id IS' => null]);
        $this->set(compact('user', 'members'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $this->requireAdmin();
        $user = $this->Users->get($id, contain: ['Roles', 'Members']);
        $isAdmin = $this->isAdmin();
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            // Restrição: apenas administradores podem alterar email e role
            if (!$isAdmin) {
                unset($data['email']);
                unset($data['roles']);
            }

            $selectedMemberIds = $data['members']['_ids'] ?? [];

            $conflictingMembers = $this->Users->Members->find()
                ->where([
                    'Members.id IN' => $selectedMemberIds,
                    'Members.user_id IS NOT' => null,
                    'Members.user_id IS NOT' => $id,
                ]);

            if ($conflictingMembers->count() > 0) {
                $this->Flash->error(__('One or more selected members are already associated with another user. Please deselect them.'));
            } else {
                // First, unlink all currently associated members.
                $this->Users->Members->updateAll(
                    ['user_id' => null],
                    ['user_id' => $id]
                );

                // Now, patch and save the new associations.
                $user = $this->Users->patchEntity($user, $data);
                if ($this->Users->save($user)) {
                    $this->Flash->success(__('The user has been saved.'));
                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error(__('The user could not be saved. Please, try again.'));
            }
        }
        $members = $this->Users->Members->find('list')->where(function ($exp, $q) use ($id) {
            return $exp->or([
                'user_id IS' => null,
                'user_id' => $id,
            ]);
        });
        $roles = $this->Users->Roles->find('list', limit: 200)->all();
        $this->set(compact('user', 'roles', 'members', 'isAdmin'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->requireAdmin();
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function login()
    {

        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();
        // regardless of POST or GET, redirect if user is logged in
        if ($result && $result->isValid()) {
            // redirect to /articles after login success
            $redirect = $this->request->getQuery('redirect', [
                'controller' => 'collectedChests',
                'action' => 'score',
            ]);
    
            return $this->redirect($redirect);
        }
        // display error if user submitted and authentication failed
        if ($this->request->is('post') && !$result->isValid()) {
            $this->Flash->error(__('Invalid email or password'));
        }
    }
    public function logout()
    {

        $result = $this->Authentication->getResult();
        // regardless of POST or GET, redirect if user is logged in
        if ($result && $result->isValid()) {
            $this->Authentication->logout();

            return $this->redirect(['controller' => 'collectedChests', 'action' => 'score']);
        }
    }

    /**
     * Change Password method
     * Permite que o usuário altere sua própria senha
     *
     * @return \Cake\Http\Response|null|void Redirects on successful change, renders view otherwise.
     */
    public function changePassword()
    {
        $userId = $this->currentUserId();
        if (!$userId) {
            throw new UnauthorizedException(__('You must be logged in to change your password.'));
        }

        $user = $this->Users->get($userId);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            
            // Validar senha atual
            $hasher = new \Authentication\PasswordHasher\DefaultPasswordHasher();
            if (!$hasher->check($data['current_password'] ?? '', $user->password)) {
                $this->Flash->error(__('Current password is incorrect.'));
                return $this->redirect($this->referer());
            }
            
            // Validar que as novas senhas coincidem
            if (empty($data['new_password']) || empty($data['confirm_password'])) {
                $this->Flash->error(__('New password and confirmation are required.'));
                return $this->redirect($this->referer());
            }
            
            if ($data['new_password'] !== $data['confirm_password']) {
                $this->Flash->error(__('New password and confirmation do not match.'));
                return $this->redirect($this->referer());
            }
            
            // Validar comprimento mínimo da senha
            if (strlen($data['new_password']) < 6) {
                $this->Flash->error(__('Password must be at least 6 characters long.'));
                return $this->redirect($this->referer());
            }
            
            // Atualizar senha
            $user->password = $data['new_password'];
            if ($this->Users->save($user)) {
                $this->Flash->success(__('Password has been changed successfully.'));
                // Redirecionar para a página atual sem o modal
                $referer = $this->referer();
                if (strpos($referer, '#') !== false) {
                    $referer = strtok($referer, '#');
                }
                return $this->redirect($referer);
            } else {
                $this->Flash->error(__('The password could not be changed. Please, try again.'));
            }
        }
        
        return $this->redirect($this->referer());
    }
}
