<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $error
 */

$this->assign('title', __('Edit Error'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Errors'), 'url' => ['action' => 'index']],
    ['title' => __('View'), 'url' => ['action' => 'view', $error->id]],
    ['title' => __('Edit')],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-edit text-primary mr-2"></i><?= __('Edit Error') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Modify error record details') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-eye mr-1"></i> ' . __('View'),
                ['action' => 'view', $error->id],
                ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i> ' . __('List Errors'),
                ['action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <h3 class="card-title font-weight-bold mb-2 mb-md-0">
                <i class="fas fa-exclamation-triangle text-warning mr-2"></i><?= __('Error # {0}', $this->Number->format($error->id)) ?>
            </h3>
        </div>

        <?= $this->Form->create($error) ?>
        <div class="card-body">
            <p class="text-muted"><?= __('Configure error record details.') ?></p>
        </div>

        <div class="card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between">
            <div class="mb-2 mb-sm-0">
                <?= $this->Form->postLink(
                    '<i class="fas fa-trash-alt mr-1"></i> ' . __('Delete'),
                    ['action' => 'delete', $error->id],
                    [
                        'confirm' => __('Are you sure you want to delete # {0}?', $error->id),
                        'class' => 'btn btn-danger',
                        'escape' => false,
                    ]
                ) ?>
            </div>
            <div class="d-flex" style="gap: 8px;">
                <?= $this->Html->link(
                    '<i class="fas fa-times mr-1"></i> ' . __('Cancel'),
                    ['action' => 'view', $error->id],
                    ['class' => 'btn btn-default', 'escape' => false]
                ) ?>
                <?= $this->Form->button(
                    '<i class="fas fa-save mr-1"></i> ' . __('Save Changes'),
                    ['class' => 'btn btn-primary', 'escapeTitle' => false]
                ) ?>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>