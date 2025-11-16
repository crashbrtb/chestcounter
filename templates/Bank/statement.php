<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Member $member
 * @var array $members
 * @var int $memberId
 * @var int $balance
 * @var \Cake\Collection\CollectionInterface|\App\Model\Entity\BankTransaction[] $transactions
 */
?>

<div class="bank statement content">
    <h3><?= __('Account statement') ?></h3>
    <p><?= __('Track every transaction registered for your members. Amounts are in millions of Silver ($).') ?></p>

    <?= $this->Form->create(null, ['type' => 'get', 'class' => 'form-inline mb-3']) ?>
        <div class="form-group mr-2">
            <?= $this->Form->control('member_id', [
                'label' => __('Member'),
                'options' => $members,
                'value' => $memberId,
                'class' => 'form-control',
            ]) ?>
        </div>
        <?= $this->Form->button(__('Refresh'), ['class' => 'btn btn-primary']) ?>
    <?= $this->Form->end() ?>

    <p><?= __('Selected member: {0}', h($member->player)) ?></p>
    <p><?= __('Current balance: {0} $', $this->Number->format($balance, ['places' => 0])) ?></p>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th><?= __('Type') ?></th>
                    <th><?= __('Amount ($)') ?></th>
                    <th><?= __('Fee ($)') ?></th>
                    <th><?= __('Net effect ($)') ?></th>
                    <th><?= __('Status') ?></th>
                    <th><?= __('Created at') ?></th>
                    <th><?= __('Approval log') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($transactions) === 0): ?>
                    <tr>
                        <td colspan="6"><?= __('No transactions registered yet.') ?></td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($transactions as $transaction): ?>
                    <?php
                        $isSource = (int)$transaction->member_id === $memberId;
                        $isDestination = (int)$transaction->destination_member_id === $memberId;

                        $label = ucfirst($transaction->type);
                        if ($transaction->type === 'transfer') {
                            if ($isSource) {
                                $label = __('Transfer to {0}', $transaction->destination_member->player ?? __('Unknown'));
                            } elseif ($isDestination) {
                                $label = __('Transfer from {0}', $transaction->member->player ?? __('Unknown'));
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

                        $netEffect = ($effectSign === '-' ? -1 : 1) * $effectValue;
                        $rowClass = $transaction->status === 'pending' ? 'table-warning' : '';
                    ?>
                    <tr class="<?= h($rowClass) ?>">
                        <td><?= h($label) ?></td>
                        <td><?= $this->Number->format($transaction->amount, ['places' => 0]) ?></td>
                        <td><?= $this->Number->format($transaction->fee, ['places' => 0]) ?></td>
                        <td><?= $this->Number->format($netEffect, ['places' => 0]) ?></td>
                        <td><?= ucfirst($transaction->status) ?></td>
                        <td><?= $transaction->created ? $transaction->created->i18nFormat('yyyy-MM-dd HH:mm') : '' ?></td>
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
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

