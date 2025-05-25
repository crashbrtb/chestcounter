<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $error
 */
?>

<?php
$this->assign('title', __('Edit Error'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Errors'), 'url' => ['action' => 'index']],
    ['title' => __('View'), 'url' => ['action' => 'view', $error->id]],
    ['title' => __('Edit')],
]);
?>

<div class="card card-primary card-outline">
    <?= $this->Form->create($error) ?>
    <div class="card-body">
        <?= $this->Form->control('error_value') ?>
    </div>
    <div class="card-footer d-flex">
        <div class="mr-auto">
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $error->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $error->id), 'class' => 'btn btn-danger']
            ) ?>
        </div>
        <div class="ml-auto">
            <?= $this->Form->button(__('Save'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Html->link(__('Cancel'), ['action' => 'view', $error->id], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>