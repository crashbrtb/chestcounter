<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Member $member
 */
?>

<?php
$this->assign('title', __('Edit Member'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Members'), 'url' => ['action' => 'index']],
    ['title' => __('View'), 'url' => ['action' => 'view', $member->id]],
    ['title' => __('Edit')],
]);
?>

<div class="card card-primary card-outline">
    <?= $this->Form->create($member) ?>
    <div class="card-body">
        <?= $this->Form->control('player') ?>
        <?= $this->Form->control('power') ?>
        <?= $this->Form->control('guards') ?>
        <?= $this->Form->control('specialists') ?>
        <?= $this->Form->control('monsters') ?>
        <?= $this->Form->control('engineers') ?>
        <?= $this->Form->control('active') ?>
        <?= $this->Form->control('created_at') ?>
        <?= $this->Form->control('modified_at') ?>
    </div>
    <div class="card-footer d-flex">
        <div class="mr-auto">
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $member->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $member->id), 'class' => 'btn btn-danger']
            ) ?>
        </div>
        <div class="ml-auto">
            <?= $this->Form->button(__('Save'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Html->link(__('Cancel'), ['action' => 'view', $member->id], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>