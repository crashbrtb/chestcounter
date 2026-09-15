<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\Routing\Router;

/**
 * Personal tokens for the EventUploader desktop tool.
 *
 * A token is shown exactly once, right after it is created: only its hash is
 * stored. Between the POST that creates it and the page that shows it, the
 * plain token waits in the session and is removed as soon as it is displayed,
 * so reloading the page neither shows it again nor creates another.
 *
 * @property \App\Model\Table\ApiTokensTable $ApiTokens
 */
class ApiTokensController extends AppController
{
    private const SESSION_KEY = 'ApiTokens.issued';

    /**
     * Every token, with who owns it and when it was last used.
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        $this->requireAdmin();

        $tokens = $this->ApiTokens->find()
            ->contain(['Users'])
            ->orderBy(['ApiTokens.created' => 'DESC'])
            ->all();

        $session = $this->request->getSession();
        $issued = $session->consume(self::SESSION_KEY);

        $this->set(compact('tokens', 'issued'));
        $this->set('siteUrl', rtrim(Router::url('/', true), '/'));
    }

    /**
     * Create a token for the signed-in administrator.
     *
     * @return \Cake\Http\Response
     */
    public function add(): Response
    {
        $this->requireAdmin();
        $this->request->allowMethod(['post']);

        [$token, $plain] = $this->ApiTokens->issue((int)$this->currentUserId(), (string)$this->request->getData('name'));
        if ($plain === null) {
            $errors = $token->getError('name');
            $this->Flash->error($errors ? implode(' ', $errors) : __('The token could not be created. Please, try again.'));

            return $this->redirect(['action' => 'index']);
        }

        $this->request->getSession()->write(self::SESSION_KEY, ['name' => $token->name, 'token' => $plain]);

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Revoke a token. It stops working immediately; the row stays for the record.
     *
     * @param string|null $id Token id.
     * @return \Cake\Http\Response
     */
    public function revoke(?string $id = null): Response
    {
        $this->requireAdmin();
        $this->request->allowMethod(['post']);

        $token = $this->ApiTokens->get($id);
        if ($token->revoked_at === null) {
            $token->set('revoked_at', DateTime::now(), ['guard' => false]);
            $this->ApiTokens->saveOrFail($token);
        }
        $this->Flash->success(__('The token "{0}" was revoked.', $token->name));

        return $this->redirect(['action' => 'index']);
    }
}
