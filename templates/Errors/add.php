<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $error
 */

$this->assign('title', __('Add Error'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Errors'), 'url' => ['action' => 'index']],
    ['title' => __('Add')],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-plus-circle text-primary mr-2"></i><?= __('Add Error') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Manually record an error log entry') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i> ' . __('List Errors'),
                ['action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-exclamation-circle text-primary mr-2"></i><?= __('Error Log') ?>
            </h3>
        </div>

        <?= $this->Form->create($error, ['valueSources' => ['query', 'context']]) ?>
        <div class="card-body">
            <p class="text-muted"><?= __('Configure error record details.') ?></p>
        </div>

        <div class="card-footer d-flex justify-content-end" style="gap: 8px;">
            <?= $this->Html->link(
                '<i class="fas fa-times mr-1"></i> ' . __('Cancel'),
                ['action' => 'index'],
                ['class' => 'btn btn-default', 'escape' => false]
            ) ?>
            <?= $this->Form->button(
                '<i class="fas fa-save mr-1"></i> ' . __('Save Error'),
                ['class' => 'btn btn-primary', 'escapeTitle' => false]
            ) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>