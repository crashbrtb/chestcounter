<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Event\EventInterface;
use Cake\Controller\Component\AuthComponent;
use Authentication\Controller\Component\AuthenticationComponent;
use Authorization\Controller\Component\AuthorizationComponent;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/4/en/controllers.html#the-app-controller
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 * @property \Authorization\Controller\Component\AuthorizationComponent $Authorization
 * @property \Cake\Controller\Component\FlashComponent $Flash
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('Authorization.Authorization');
        $this->loadComponent('Flash');
        $this->Authentication->allowUnauthenticated(['score','history']);
        
        // Configuração do CakeLTE
        $this->viewBuilder()->setLayout('CakeLte/layout/default');

        // Define as configurações do tema

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/4/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }

    /**
     * beforeFilter callback.
     *
     * @param \Cake\Event\EventInterface $event An Event instance.
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        // for all controllers in our application, make index and view actions
        // require a logged in user.whitelist all public actions to allow all users to access them
        // $this->Authentication->addUnauthenticatedActions([
        //     'login', 'register', 'forgotPassword', 'resetPassword', // Adicione aqui actions públicas do UsersController
        //     'display' // Exemplo para PagesController::display
        // ]);

        // Pular verificação de autorização para actions específicas se necessário
        // Exemplo: se UsersController::login não precisa de autorização.
        // if ($this->request->getParam('controller') === 'Users' && $this->request->getParam('action') === 'login') {
        //    $this->Authorization->skipAuthorization();
        // }
        $controller = $this->request->getParam('controller');
        $action = $this->request->getParam('action');
        if ($controller === 'Pages' && $action === 'display') {
            // Você pode ser mais específico aqui se apenas algumas páginas do PagesController::display são públicas
            // Por exemplo, verificando $this->request->getParam('pass.0') === 'underconstruction'
            // Mas, para simplificar, se todas as 'PagesController::display' são públicas:
            $this->Authentication->addUnauthenticatedActions(['display']);
        }
    }

    public function beforeRender(EventInterface $event)
    {
        parent::beforeRender($event);
        if($this->request->getParam('prefix') == 'Admin'){
            if($this->request->getAttribute('identity') != null && $this->request->getAttribute('identity')->get('role') == 'admin'){
                $this->viewBuilder()->setLayout('admin');
            }
        }
    }

    public function changeLanguage($lang = null)
    {
        if ($lang && in_array($lang, ['en_US', 'pt_BR'])) {
            $this->request->getSession()->write('Config.language', $lang);
            \Cake\I18n\I18n::setLocale($lang);
        }
        return $this->redirect($this->referer());
    }
}
