<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Config $config
 */
?>

<?php
$this->assign('title', __('Edit Config'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Config'), 'url' => ['action' => 'index']],
    ['title' => __('View'), 'url' => ['action' => 'view', $config->id]],
    ['title' => __('Edit')],
]);
?>

<div class="card card-primary card-outline">
    <?= $this->Form->create($config) ?>
    <div class="card-body">
        <?= $this->Form->control('param') ?>
        <?= $this->Form->control('value') ?>
        <?= $this->Form->control('description') ?>
    </div>
    <div class="card-footer d-flex">
        <div class="mr-auto">
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $config->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $config->id), 'class' => 'btn btn-danger']
            ) ?>
        </div>
        <div class="ml-auto">
            <?= $this->Form->button(__('Save'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Html->link(__('Cancel'), ['action' => 'view', $config->id], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>