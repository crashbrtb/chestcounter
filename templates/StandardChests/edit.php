<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\StandardChest $standardChest
 */
?>

<?php
$this->assign('title', __('Edit Standard Chest'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Standard Chests'), 'url' => ['action' => 'index']],
    ['title' => __('View'), 'url' => ['action' => 'view', $standardChest->id]],
    ['title' => __('Edit')],
]);
?>

<div class="card card-primary card-outline">
    <?= $this->Form->create($standardChest) ?>
    <div class="card-body">
        <?= $this->Form->control('source') ?>
        <?= $this->Form->control('score') ?>
    </div>
    <div class="card-footer d-flex">
        <div class="mr-auto">
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $standardChest->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $standardChest->id), 'class' => 'btn btn-danger']
            ) ?>
        </div>
        <div class="ml-auto">
            <?= $this->Form->button(__('Save'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Html->link(__('Cancel'), ['action' => 'view', $standardChest->id], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>