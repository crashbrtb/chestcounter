<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Role $role
 * @var iterable<\App\Model\Entity\User> $users
 */

$this->assign('title', __('Add Role'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Roles'), 'url' => ['action' => 'index']],
    ['title' => __('Add')],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-shield-alt text-primary mr-2"></i><?= __('Add Role') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Define a new authorization role and its permissions') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i> ' . __('List Roles'),
                ['action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-plus-circle text-primary mr-2"></i><?= __('New Role Details') ?>
            </h3>
        </div>

        <?= $this->Form->create($role, ['valueSources' => ['query', 'context']]) ?>
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
                                'placeholder' => __('e.g. manager'),
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
                                'placeholder' => __('e.g. role_manager'),
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
                            'placeholder' => __('Describe what this role permits...'),
                            'templates' => ['inputContainer' => '{{content}}'],
                        ]) ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold"><?= __('Assign Users') ?></label>
                        <?= $this->Form->control('users._ids', [
                            'options' => $users,
                            'multiple' => true,
                            'label' => false,
                            'class' => 'form-control',
                            'style' => 'min-height: 200px;',
                            'templates' => ['inputContainer' => '{{content}}'],
                        ]) ?>
                        <small class="form-text text-muted">
                            <?= __('Hold Ctrl (or Cmd) to select multiple users.') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-end" style="gap: 8px;">
            <?= $this->Html->link(
                '<i class="fas fa-times mr-1"></i> ' . __('Cancel'),
                ['action' => 'index'],
                ['class' => 'btn btn-default', 'escape' => false]
            ) ?>
            <?= $this->Form->button(
                '<i class="fas fa-save mr-1"></i> ' . __('Save Role'),
                ['class' => 'btn btn-primary', 'escapeTitle' => false]
            ) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>