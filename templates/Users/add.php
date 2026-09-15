<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var array $members
 */

$this->assign('title', __('Add User'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Users'), 'url' => ['action' => 'index']],
    ['title' => __('Add')],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-user-plus text-primary mr-2"></i><?= __('Add User') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Create a new system user account and optionally link clan members') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i> ' . __('List Users'),
                ['action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-user-circle text-primary mr-2"></i><?= __('Account Registration') ?>
            </h3>
        </div>

        <?= $this->Form->create($user, ['valueSources' => ['query', 'context']]) ?>
        <div class="card-body">
            <div class="row">
                <!-- Coluna Esquerda: Informações da Conta -->
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h5 class="text-primary font-weight-bold mb-3 pb-2 border-bottom">
                        <i class="fas fa-user mr-2"></i><?= __('Account Details') ?>
                    </h5>

                    <!-- Nome -->
                    <div class="form-group">
                        <label for="name" class="font-weight-bold"><?= __('Name') ?> <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <?= $this->Form->control('name', [
                                'label' => false,
                                'class' => 'form-control',
                                'required' => true,
                                'placeholder' => __('User full name'),
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="font-weight-bold"><?= __('Email') ?> <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            </div>
                            <?= $this->Form->control('email', [
                                'label' => false,
                                'class' => 'form-control',
                                'required' => true,
                                'placeholder' => __('name@example.com'),
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                    </div>

                    <!-- Senha -->
                    <div class="form-group">
                        <label for="password" class="font-weight-bold"><?= __('Password') ?> <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                            </div>
                            <?= $this->Form->control('password', [
                                'label' => false,
                                'class' => 'form-control',
                                'required' => true,
                                'autocomplete' => 'new-password',
                                'placeholder' => __('Enter a secure password'),
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                    </div>

                    <!-- Status e Acesso -->
                    <div class="form-group p-3 rounded bg-light border">
                        <label class="font-weight-bold d-block mb-2"><?= __('Status & Access') ?></label>
                        <?= $this->Form->control('active', [
                            'type' => 'checkbox',
                            'custom' => true,
                            'label' => __('Active (Allow user to log in and access the system)'),
                            'default' => 1,
                            'checked' => true,
                        ]) ?>
                        <small class="form-text text-muted mt-2">
                            <i class="fas fa-info-circle mr-1"></i><?= __('The user will be created with default role: <strong>user</strong>.') ?>
                        </small>
                    </div>
                </div>

                <!-- Coluna Direita: Membros do Clã Associados -->
                <div class="col-lg-6">
                    <h5 class="text-primary font-weight-bold mb-3 pb-2 border-bottom">
                        <i class="fas fa-shield-alt mr-2"></i><?= __('Linked Clan Members') ?>
                    </h5>

                    <div class="form-group">
                        <label class="font-weight-bold"><?= __('Clan Members') ?></label>
                        <?= $this->Form->control('members._ids', [
                            'options' => $members,
                            'multiple' => true,
                            'label' => false,
                            'class' => 'form-control',
                            'style' => 'min-height: 220px;',
                            'templates' => ['inputContainer' => '{{content}}'],
                        ]) ?>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle mr-1"></i><?= __('Hold Ctrl (or Cmd) to select or deselect multiple members.') ?>
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
                '<i class="fas fa-save mr-1"></i> ' . __('Save User'),
                ['class' => 'btn btn-primary', 'escapeTitle' => false]
            ) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>