<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Role $role
 * @var iterable<\App\Model\Entity\User> $users
 */

$this->assign('title', __('Edit Role'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Roles'), 'url' => ['action' => 'index']],
    ['title' => __('View'), 'url' => ['action' => 'view', $role->id]],
    ['title' => __('Edit')],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-user-shield text-primary mr-2"></i><?= __('Edit Role') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Modify role details, description, and assigned users') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-eye mr-1"></i> ' . __('View'),
                ['action' => 'view', $role->id],
                ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i> ' . __('List Roles'),
                ['action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <h3 class="card-title font-weight-bold mb-2 mb-md-0">
                <i class="fas fa-shield-alt text-primary mr-2"></i><?= h($role->name) ?>
                <span class="text-muted small ml-1">#<?= $this->Number->format($role->id) ?></span>
            </h3>
        </div>

        <?= $this->Form->create($role) ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name" class="font-weight-bold"><?= __('Role Name') ?> <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            </div>
                            <?= $this->Form->control('name', [
                                'label' => false,
                                'class' => 'form-control',
                                'required' => true,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="alias" class="font-weight-bold"><?= __('Alias / Identifier') ?> <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-fingerprint"></i></span>
                            </div>
                            <?= $this->Form->control('alias', [
                                'label' => false,
                                'class' => 'form-control',
                                'required' => true,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description" class="font-weight-bold"><?= __('Description') ?></label>
                        <?= $this->Form->control('description', [
                            'type' => 'textarea',
                            'label' => false,
                            'class' => 'form-control',
                            'rows' => 3,
                            'templates' => ['inputContainer' => '{{content}}'],
                        ]) ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold"><?= __('Assigned Users') ?></label>
                        <?= $this->Form->control('users._ids', [
                            'options' => $users,
                            'multiple' => true,
                            'label' => false,
                            'class' => 'form-control',
                            'style' => 'min-height: 200px;',
                            'templates' => ['inputContainer' => '{{content}}'],
                        ]) ?>
                        <small class="form-text text-muted">
                            <?= __('Hold Ctrl (or Cmd) to select or deselect multiple users.') ?>
                        </small>
                    </div>
                </div>

                <div class="col-12 mt-2 pt-2 border-top d-flex justify-content-between text-muted small">
                    <span><i class="fas fa-clock mr-1"></i><?= __('Created: {0}', !empty($role->created) ? $role->created->format('d/m/Y H:i') : '-') ?></span>
                    <span><i class="fas fa-history mr-1"></i><?= __('Modified: {0}', !empty($role->modified) ? $role->modified->format('d/m/Y H:i') : '-') ?></span>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between">
            <div class="mb-2 mb-sm-0">
                <?= $this->Form->postLink(
                    '<i class="fas fa-trash-alt mr-1"></i> ' . __('Delete'),
                    ['action' => 'delete', $role->id],
                    [
                        'confirm' => __('Are you sure you want to delete # {0}?', $role->id),
                        'class' => 'btn btn-danger',
                        'escape' => false,
                    ]
                ) ?>
            </div>
            <div class="d-flex" style="gap: 8px;">
                <?= $this->Html->link(
                    '<i class="fas fa-times mr-1"></i> ' . __('Cancel'),
                    ['action' => 'index'],
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