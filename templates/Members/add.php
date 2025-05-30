<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Member $member
 */
?>

<?php
$this->assign('title', __('Add Member'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Members'), 'url' => ['action' => 'index']],
    ['title' => __('Add')],
]);
?>

<div class="card card-primary card-outline">
    <?= $this->Form->create($member, ['valueSources' => ['query', 'context']]) ?>
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
        <div class="ml-auto">
            <?= $this->Form->button(__('Save'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>