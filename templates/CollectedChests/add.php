<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CollectedChest $collectedChest
 */
?>

<?php
$this->assign('title', __('Add Collected Chest'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Collected Chests'), 'url' => ['action' => 'index']],
    ['title' => __('Add')],
]);
?>

<div class="card card-primary card-outline">
    <?= $this->Form->create($collectedChest, ['valueSources' => ['query', 'context']]) ?>
    <div class="card-body">
        <?= $this->Form->control('name') ?>
        <?= $this->Form->control('player') ?>
        <?= $this->Form->control('source') ?>
        <?= $this->Form->control('type') ?>
        <?= $this->Form->control('collected_at') ?>
    </div>
    <div class="card-footer d-flex">
        <div class="ml-auto">
            <?= $this->Form->button(__('Save'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>