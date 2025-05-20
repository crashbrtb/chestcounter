<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\StandardChest> $standardChests
 */
?>
<div class="standardChests index content">
    <?= $this->Html->link(__('New Standard Chest'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Standard Chests') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('source') ?></th>
                    <th><?= $this->Paginator->sort('score') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($standardChests as $standardChest): ?>
                <tr>
                    <td><?= $this->Number->format($standardChest->id) ?></td>
                    <td><?= h($standardChest->source) ?></td>
                    <td><?= $this->Number->format($standardChest->score) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $standardChest->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $standardChest->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $standardChest->id], ['confirm' => __('Are you sure you want to delete # {0}?', $standardChest->id)]) ?>
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