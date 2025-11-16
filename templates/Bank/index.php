<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Collection\CollectionInterface|\App\Model\Entity\Member[] $members
 * @var float $totalSilver
 * @var float $totalFees
 * @var bool $isAdmin
 */
?>
<div class="bank index content">
    <div class="actions mb-3">
        <?= $this->Html->link('Deposit Silver', ['action' => 'deposit'], ['class' => 'btn btn-success mr-2']) ?>
        <?= $this->Html->link('Withdraw Silver', ['action' => 'withdraw'], ['class' => 'btn btn-warning mr-2']) ?>
        <?= $this->Html->link('Transfer Silver', ['action' => 'transfer'], ['class' => 'btn btn-primary']) ?>
    </div>

    <h3><?= __('Bank Members Overview') ?></h3>
    <p><?= __('All values are stored in millions of Silver ($).') ?></p>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th><?= __('Player') ?></th>
                    <th><?= __('Balance ($)') ?></th>
                    <th><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                    <?php $balance = (int)round($member->bank_account->balance ?? 0); ?>
                    <tr>
                        <td>
                            <?php if ($isAdmin): ?>
                                <?= $this->Html->link(h($member->player), ['action' => 'history', $member->id]) ?>
                            <?php else: ?>
                                <?= h($member->player) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $this->Number->format($balance, ['places' => 0]) ?> $
                        </td>
                        <td>
                            <?php if ($isAdmin): ?>
                                <?= $this->Html->link('View history', ['action' => 'history', $member->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                            <?php else: ?>
                                <span class="text-muted"><?= __('Admin only') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?= __('Total Silver in Bank') ?></h5>
                    <p class="card-text display-6">
                        <?= $this->Number->format($totalSilver, ['places' => 0]) ?> $
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?= __('Total Fees Collected') ?></h5>
                    <p class="card-text display-6">
                        <?= $this->Number->format($totalFees, ['places' => 0]) ?> $
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

