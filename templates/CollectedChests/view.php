<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CollectedChest $collectedChest
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Collected Chest'), ['action' => 'edit', $collectedChest->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Collected Chest'), ['action' => 'delete', $collectedChest->id], ['confirm' => __('Are you sure you want to delete # {0}?', $collectedChest->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Collected Chests'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Collected Chest'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="collectedChests view content">
            <h3><?= h($collectedChest->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($collectedChest->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Player') ?></th>
                    <td><?= h($collectedChest->player) ?></td>
                </tr>
                <tr>
                    <th><?= __('Source') ?></th>
                    <td><?= h($collectedChest->source) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($collectedChest->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Type') ?></th>
                    <td><?= $this->Number->format($collectedChest->type) ?></td>
                </tr>
                <tr>
                    <th><?= __('Collected At') ?></th>
                    <td><?= h($collectedChest->collected_at) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>