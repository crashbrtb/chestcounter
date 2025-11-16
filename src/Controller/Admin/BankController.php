<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\BankTransaction;
use App\Model\Table\BankAccountsTable;
use App\Model\Table\BankApprovalLogsTable;
use App\Model\Table\BankTransactionsTable;
use App\Model\Table\MembersTable;
use App\Service\BankingService;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use RuntimeException;

class BankController extends AppController
{
    protected BankingService $banking;
    protected BankTransactionsTable $BankTransactions;
    protected BankAccountsTable $BankAccounts;
    protected BankApprovalLogsTable $BankApprovalLogs;
    protected MembersTable $Members;

    public function initialize(): void
    {
        parent::initialize();

        $locator = TableRegistry::getTableLocator();
        $this->BankTransactions = $locator->get('BankTransactions');
        $this->BankAccounts = $locator->get('BankAccounts');
        $this->BankApprovalLogs = $locator->get('BankApprovalLogs');
        $this->Members = $locator->get('Members');

        $this->banking = new BankingService($this->BankAccounts);
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $userId = $this->currentUserId();
        if (!$userId) {
            $this->Flash->error(__('Please sign in as administrator.'));
            return $this->redirect(['prefix' => false, 'controller' => 'Users', 'action' => 'login']);
        }

        if (!$this->isAdmin($userId)) {
            throw new ForbiddenException(__('Only administrators can access the banking approvals.'));
        }
    }

    public function approvals()
    {
        $pendingTransactions = $this->BankTransactions->find()
            ->contain([
                'Members',
                'Users',
                'BankApprovalLogs' => ['AdminUsers'],
            ])
            ->where([
                'BankTransactions.status' => BankTransactionsTable::STATUS_PENDING,
                'BankTransactions.type IN' => [
                    BankTransactionsTable::TYPE_DEPOSIT,
                    BankTransactionsTable::TYPE_WITHDRAWAL,
                ],
            ])
            ->orderAsc('BankTransactions.created')
            ->all();

        $this->set(compact('pendingTransactions'));
    }

    public function approve(int $id)
    {
        $this->request->allowMethod(['post']);
        $this->autoRender = false;
        $this->viewBuilder()->disableAutoLayout();

        // Pre-set redirect response to prevent error page rendering
        $redirectResponse = $this->redirect(['prefix' => 'Admin', 'controller' => 'Bank', 'action' => 'approvals']);

        try {
            $transaction = $this->getPendingTransaction($id);

            if ($transaction->status !== BankTransactionsTable::STATUS_PENDING) {
                $this->Flash->error(__('This transaction is not pending approval.'));
                return $redirectResponse;
            }

            try {
                $this->applyTransactionEffect($transaction);
            } catch (RuntimeException $exception) {
                $this->Flash->error($exception->getMessage());
                return $redirectResponse;
            }

            $transaction->status = BankTransactionsTable::STATUS_APPROVED;
            if ($this->BankTransactions->save($transaction)) {
                try {
                    $this->logApproval($transaction, 'approved');
                } catch (\Exception $e) {
                    // Log error but don't fail the approval
                    \Cake\Log\Log::error('Failed to log approval: ' . $e->getMessage());
                }
                $this->Flash->success(__('Transaction approved and applied successfully.'));
            } else {
                $this->Flash->error(__('Unable to approve this transaction.'));
            }
        } catch (NotFoundException $e) {
            $this->Flash->error(__('Transaction not found.'));
        } catch (ForbiddenException $e) {
            $this->Flash->error($e->getMessage());
        } catch (MethodNotAllowedException $e) {
            $this->Flash->error($e->getMessage());
        } catch (\Exception $e) {
            $this->Flash->error(__('An error occurred while processing the approval.'));
            \Cake\Log\Log::error('Approval error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
        }

        return $redirectResponse;
    }

    public function reject(int $id)
    {
        $this->request->allowMethod(['post']);
        $this->autoRender = false;

        $transaction = $this->getPendingTransaction($id);
        $transaction->status = BankTransactionsTable::STATUS_REJECTED;

        if ($this->BankTransactions->save($transaction)) {
            $details = [];
            $reason = trim((string)($this->request->getData('reason') ?? ''));
            if ($reason !== '') {
                $details['reason'] = $reason;
            }

            $this->logApproval($transaction, 'rejected', $details ?: null);
            $this->Flash->success(__('Transaction rejected.'));
        } else {
            $this->Flash->error(__('Unable to reject this transaction.'));
        }

        return $this->redirect(['prefix' => 'Admin', 'controller' => 'Bank', 'action' => 'approvals']);
    }

    protected function getPendingTransaction(int $id): BankTransaction
    {
        $transaction = $this->BankTransactions->find()
            ->where(['BankTransactions.id' => $id])
            ->contain(['Members'])
            ->first();

        if (!$transaction) {
            throw new NotFoundException(__('Transaction not found.'));
        }

        if ($transaction->status !== BankTransactionsTable::STATUS_PENDING) {
            throw new MethodNotAllowedException(__('This transaction is not pending.'));
        }

        if (!in_array($transaction->type, [BankTransactionsTable::TYPE_DEPOSIT, BankTransactionsTable::TYPE_WITHDRAWAL], true)) {
            throw new MethodNotAllowedException(__('Only deposits and withdrawals require approval.'));
        }

        return $transaction;
    }

    protected function applyTransactionEffect(BankTransaction $transaction): void
    {
        if ($transaction->type === BankTransactionsTable::TYPE_DEPOSIT) {
            $finalAmount = (float)$transaction->final_amount;
            $this->banking->addToBalance($transaction->member_id, (int)round($finalAmount));
            return;
        }

        if ($transaction->type === BankTransactionsTable::TYPE_WITHDRAWAL) {
            $finalAmount = (float)$transaction->final_amount;
            $this->banking->subtractFromBalance($transaction->member_id, (int)round($finalAmount));
            return;
        }

        throw new RuntimeException(__('Unsupported transaction type.'));
    }

    protected function logApproval(BankTransaction $transaction, string $action, ?array $details = null): void
    {
        $log = $this->BankApprovalLogs->newEntity([
            'bank_transaction_id' => $transaction->id,
            'admin_user_id' => $this->currentUserId(),
            'action' => $action,
            'original_values' => $details ? json_encode($details) : null,
        ]);
        
        if (!$this->BankApprovalLogs->save($log)) {
            \Cake\Log\Log::warning('Failed to save approval log for transaction ' . $transaction->id);
        }
    }

    protected function filterInteger($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        $stringValue = trim((string)$value);
        if (!preg_match('/^-?\d+$/', $stringValue)) {
            return null;
        }

        return (int)$stringValue;
    }
}

