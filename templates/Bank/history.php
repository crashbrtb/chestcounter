<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Member $member
 * @var \Cake\Collection\CollectionInterface|\App\Model\Entity\BankTransaction[] $transactions
 * @var int $memberId
 */

$balance = (int)round($member->bank_account->balance ?? 0);
?>

<div class="bank history content">
    <h3><?= __('Transaction history for {0}', h($member->player)) ?></h3>
    <p><?= __('Current balance: {0} $', $this->Number->format($balance, ['places' => 0])) ?></p>
    <p><?= __('Values are saved in millions of Silver. Positive values add Silver, negative values remove it.') ?></p>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th><?= __('Type') ?></th>
                    <th><?= __('Amount ($)') ?></th>
                    <th><?= __('Fee ($)') ?></th>
                    <th><?= __('Net effect ($)') ?></th>
                    <th><?= __('Status') ?></th>
                    <th><?= __('Comment') ?></th>
                    <th><?= __('Approval log') ?></th>
                    <th><?= __('Created at') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($transactions) === 0): ?>
                    <tr>
                        <td colspan="7"><?= __('No transactions found.') ?></td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($transactions as $transaction): ?>
                    <?php
                        $isSource = (int)$transaction->member_id === $memberId;
                        $isDestination = (int)$transaction->destination_member_id === $memberId;
                        $typeLabel = ucfirst($transaction->type);

                        if ($transaction->type === 'transfer') {
                            if ($isSource) {
                                $target = $transaction->destination_member->player ?? __('Unknown');
                                $typeLabel = __('Transfer to {0}', h($target));
                            } elseif ($isDestination) {
                                $origin = $transaction->member->player ?? __('Unknown');
                                $typeLabel = __('Transfer from {0}', h($origin));
                            }
                        }

                        $effectValue = (int)$transaction->final_amount;
                        $effectSign = '+';

                        if ($transaction->type === 'withdrawal') {
                            $effectSign = '-';
                        } elseif ($transaction->type === 'transfer') {
                            if ($isSource) {
                                $effectSign = '-';
                                $effectValue = (int)$transaction->amount + (int)$transaction->fee;
                            } elseif ($isDestination) {
                                $effectSign = '+';
                                $effectValue = (int)$transaction->amount;
                            }
                        }
                        if ($transaction->type === 'deposit') {
                            $effectSign = '+';
                        }

                        $netEffect = ($effectSign === '-' ? -1 : 1) * $effectValue;
                    ?>
                    <tr>
                        <td><?= $typeLabel ?></td>
                        <td><?= $this->Number->format($transaction->amount, ['places' => 0]) ?></td>
                        <td><?= $this->Number->format($transaction->fee, ['places' => 0]) ?></td>
                        <td><?= $this->Number->format($netEffect, ['places' => 0]) ?></td>
                        <td><?= ucfirst($transaction->status) ?></td>
                        <td><?= h($transaction->description ?? '') ?></td>
                        <td>
                            <?php if (!empty($transaction->bank_approval_logs)): ?>
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
                            <?php else: ?>
                                <?= __('No log entries') ?>
                            <?php endif; ?>
                        </td>
                        <td><?= $transaction->created ? $transaction->created->i18nFormat('yyyy-MM-dd HH:mm') : '' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?= $this->Html->link(__('Back to overview'), ['action' => 'index'], ['class' => 'btn btn-secondary mt-3']) ?>
</div>

