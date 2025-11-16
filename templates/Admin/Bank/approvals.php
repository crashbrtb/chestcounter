<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Collection\CollectionInterface|\App\Model\Entity\BankTransaction[] $pendingTransactions
 */
?>

<div class="bank approvals content">
    <h3><?= __('Pending deposits and withdrawals') ?></h3>
    <p><?= __('Approve or reject each transaction. Approved entries will update the member balance immediately.') ?></p>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th><?= __('Type') ?></th>
                    <th><?= __('Member') ?></th>
                    <th><?= __('Requested by') ?></th>
                    <th><?= __('Amount ($)') ?></th>
                    <th><?= __('Fee ($)') ?></th>
                    <th><?= __('Net effect ($)') ?></th>
                    <th><?= __('Created at') ?></th>
                    <th><?= __('Comment') ?></th>
                    <th><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($pendingTransactions) === 0): ?>
                    <tr>
                        <td colspan="9"><?= __('No transactions awaiting approval.') ?></td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($pendingTransactions as $transaction): ?>
                    <?php
                        $netEffect = (int)$transaction->final_amount;
                        if ($transaction->type === 'withdrawal') {
                            $netEffect = -$netEffect;
                        }
                    ?>
                    <tr>
                        <td><?= ucfirst($transaction->type) ?></td>
                        <td><?= h($transaction->member->player ?? __('Unknown')) ?></td>
                        <td><?= h($transaction->user->name ?? __('Unknown')) ?></td>
                        <td><?= $this->Number->format($transaction->amount, ['places' => 0]) ?></td>
                        <td><?= $this->Number->format($transaction->fee, ['places' => 0]) ?></td>
                        <td><?= $this->Number->format($netEffect, ['places' => 0]) ?></td>
                        <td><?= $transaction->created ? $transaction->created->i18nFormat('yyyy-MM-dd HH:mm') : '' ?></td>
                        <td><?= h($transaction->description ?? '') ?></td>
                        <td class="text-nowrap">
                            <?= $this->Form->postLink(
                                __('Approve'),
                                ['action' => 'approve', $transaction->id],
                                ['class' => 'btn btn-sm btn-success mb-1', 'confirm' => __('Approve this transaction?')]
                            ) ?>

                            <?= $this->Form->create(null, [
                                'url' => ['action' => 'reject', $transaction->id],
                                'style' => 'display:inline-block',
                                'class' => 'mb-1',
                            ]) ?>
                                <?= $this->Form->control('reason', [
                                    'label' => false,
                                    'placeholder' => __('Reason (optional)'),
                                    'maxlength' => 255,
                                    'class' => 'form-control form-control-sm mb-1',
                                ]) ?>
                                <?= $this->Form->button(__('Reject'), ['class' => 'btn btn-sm btn-danger']) ?>
                            <?= $this->Form->end() ?>

                        <?php if (!empty($transaction->bank_approval_logs)): ?>
                            <div class="mt-2 text-left">
                                <strong><?= __('Approval log:') ?></strong>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($transaction->bank_approval_logs as $log): ?>
                                        <li>
                                            <?= ($log->created ? $log->created->i18nFormat('yyyy-MM-dd HH:mm') : '') ?>
                                            - <?= ucfirst($log->action) ?>
                                            <?php if (!empty($log->admin_user->name)): ?>
                                                (<?= h($log->admin_user->name) ?>)
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

