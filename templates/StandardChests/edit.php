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
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $standardChest->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $standardChest->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Standard Chests'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="standardChests form content">
            <?= $this->Form->create($standardChest) ?>
            <fieldset>
                <legend><?= __('Edit Standard Chest') ?></legend>
                <?php
                    echo $this->Form->control('source');
                    echo $this->Form->control('score');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
