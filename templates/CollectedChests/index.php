<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\CollectedChest> $collectedChests
 */
?>
<div class="collectedChests index content">
    <?= $this->Html->link(__('New Collected Chest'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Collected Chests') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('player') ?></th>
                    <th><?= $this->Paginator->sort('source') ?></th>
                    <th><?= $this->Paginator->sort('type') ?></th>
                    <th><?= $this->Paginator->sort('collected_at') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($collectedChests as $collectedChest): ?>
                <tr>
                    <td><?= $this->Number->format($collectedChest->id) ?></td>
                    <td><?= h($collectedChest->name) ?></td>
                    <td><?= h($collectedChest->player) ?></td>
                    <td><?= h($collectedChest->source) ?></td>
                    <td><?= $this->Number->format($collectedChest->type) ?></td>
                    <td><?= h($collectedChest->collected_at) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $collectedChest->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $collectedChest->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $collectedChest->id], ['confirm' => __('Are you sure you want to delete # {0}?', $collectedChest->id)]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>