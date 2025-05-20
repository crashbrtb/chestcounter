<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\StandardChest $standardChest
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Standard Chest'), ['action' => 'edit', $standardChest->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Standard Chest'), ['action' => 'delete', $standardChest->id], ['confirm' => __('Are you sure you want to delete # {0}?', $standardChest->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Standard Chests'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Standard Chest'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="standardChests view content">
            <h3><?= h($standardChest->id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Source') ?></th>
                    <td><?= h($standardChest->source) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($standardChest->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Score') ?></th>
                    <td><?= $this->Number->format($standardChest->score) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>