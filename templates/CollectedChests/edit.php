<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CollectedChest $collectedChest
 */
?>

<?php
$this->assign('title', __('Edit Collected Chest'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Collected Chests'), 'url' => ['action' => 'index']],
    ['title' => __('View'), 'url' => ['action' => 'view', $collectedChest->id]],
    ['title' => __('Edit')],
]);
?>

<div class="card card-primary card-outline">
    <?= $this->Form->create($collectedChest) ?>
    <div class="card-body">
        <?= $this->Form->control('name') ?>
        <?= $this->Form->control('player') ?>
        <?= $this->Form->control('source') ?>
        <?= $this->Form->control('type') ?>
        <?= $this->Form->control('collected_at') ?>
    </div>
    <div class="card-footer d-flex">
        <div class="mr-auto">
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $collectedChest->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $collectedChest->id), 'class' => 'btn btn-danger']
            ) ?>
        </div>
        <div class="ml-auto">
            <?= $this->Form->button(__('Save'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Html->link(__('Cancel'), ['action' => 'view', $collectedChest->id], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>