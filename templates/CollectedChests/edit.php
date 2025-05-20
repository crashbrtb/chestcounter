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
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $collectedChest->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $collectedChest->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Collected Chests'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="collectedChests form content">
            <?= $this->Form->create($collectedChest) ?>
            <fieldset>
                <legend><?= __('Edit Collected Chest') ?></legend>
                <?php
                    echo $this->Form->control('name');
                    echo $this->Form->control('player');
                    echo $this->Form->control('source');
                    echo $this->Form->control('type');
                    echo $this->Form->control('collected_at');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
