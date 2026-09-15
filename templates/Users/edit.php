<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var bool $isAdmin
 */

// Verificar se o usuário atual é administrador (se não foi passado pelo controller)
if (!isset($isAdmin)) {
    $identity = $this->request->getAttribute('identity');
    $isLoggedIn = $identity !== null;
    $isAdmin = false;
    
    if ($isLoggedIn) {
        $userAssociatedRoles = $identity->get('roles');
        if (!empty($userAssociatedRoles) && (is_array($userAssociatedRoles) || $userAssociatedRoles instanceof \Traversable)) {
            foreach ($userAssociatedRoles as $roleEntity) {
                if (is_object($roleEntity) && isset($roleEntity->name) && $roleEntity->name === 'admin') {
                    $isAdmin = true;
                    break;
                }
            }
        }
    }
}
?>

<?php
$this->assign('title', __('Edit User'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Users'), 'url' => ['action' => 'index']],
    ['title' => __('View'), 'url' => ['action' => 'view', $user->id]],
    ['title' => __('Edit')],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-user-edit text-primary mr-2"></i><?= __('Edit User') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Manage account details, permissions, and linked clan members') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-eye mr-1"></i> ' . __('View'),
                ['action' => 'view', $user->id],
                ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i> ' . __('List Users'),
                ['action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <h3 class="card-title font-weight-bold mb-2 mb-md-0">
                <i class="fas fa-id-card text-primary mr-2"></i><?= h($user->name) ?>
                <span class="text-muted small ml-1">#<?= $this->Number->format($user->id) ?></span>
            </h3>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <?php if ($user->active): ?>
                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> <?= __('Active') ?></span>
                <?php else: ?>
                    <span class="badge badge-warning text-dark"><i class="fas fa-clock mr-1"></i> <?= __('Pending') ?></span>
                <?php endif; ?>
                <?php if (!empty($user->google_id)): ?>
                    <span class="badge badge-light border"><i class="fab fa-google text-danger mr-1"></i> <?= __('Linked with Google') ?></span>
                <?php endif; ?>
            </div>
        </div>

        <?= $this->Form->create($user) ?>
        <div class="card-body">
            <div class="row">
                <!-- Coluna Esquerda: Informações da Conta -->
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h5 class="text-primary font-weight-bold mb-3 pb-2 border-bottom">
                        <i class="fas fa-user-circle mr-2"></i><?= __('Account Details') ?>
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
                                'disabled' => !$isAdmin,
                                'readonly' => !$isAdmin,
                                'required' => true,
                                'placeholder' => __('name@example.com'),
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <?php if (!$isAdmin): ?>
                            <small class="form-text text-muted"><i class="fas fa-lock mr-1"></i><?= __('Only administrators can change the email.') ?></small>
                        <?php endif; ?>
                    </div>

                    <!-- Senha -->
                    <div class="form-group">
                        <label for="password" class="font-weight-bold"><?= __('Password') ?></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                            </div>
                            <?= $this->Form->control('password', [
                                'label' => false,
                                'class' => 'form-control',
                                'value' => '',
                                'autocomplete' => 'new-password',
                                'placeholder' => __('Leave blank to keep current password'),
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle mr-1"></i><?= __('Leave blank if you do not want to change the password.') ?>
                        </small>
                    </div>

                    <!-- Status e Acesso -->
                    <div class="form-group p-3 rounded bg-light border">
                        <label class="font-weight-bold d-block mb-2"><?= __('Status & Access') ?></label>
                        <?= $this->Form->control('active', [
                            'type' => 'checkbox',
                            'custom' => true,
                            'label' => __('Active (Allow user to log in and access the system)'),
                            'disabled' => !$isAdmin,
                        ]) ?>
                        <?php if (!$isAdmin): ?>
                            <small class="form-text text-muted mt-1"><i class="fas fa-lock mr-1"></i><?= __('Only administrators can change the status.') ?></small>
                        <?php endif; ?>

                        <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between">
                            <span class="text-muted small font-weight-bold">
                                <i class="fab fa-google mr-1 text-danger"></i><?= __('Google Account') ?>
                            </span>
                            <?php if (!empty($user->google_id)): ?>
                                <span class="badge badge-success p-1">
                                    <i class="fas fa-check-circle mr-1"></i><?= __('Linked with Google OAuth') ?>
                                </span>
                            <?php else: ?>
                                <span class="badge badge-secondary p-1">
                                    <i class="fas fa-times-circle mr-1"></i><?= __('Not Linked') ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Coluna Direita: Permissões e Membros -->
                <div class="col-lg-6">
                    <h5 class="text-primary font-weight-bold mb-3 pb-2 border-bottom">
                        <i class="fas fa-user-shield mr-2"></i><?= __('Roles & Clan Association') ?>
                    </h5>

                    <!-- Role -->
                    <div class="form-group">
                        <label class="font-weight-bold"><?= __('Roles') ?> <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-shield-alt"></i></span>
                            </div>
                            <?= $this->Form->control('roles._ids', [
                                'options' => $roles,
                                'label' => false,
                                'class' => 'form-control',
                                'disabled' => !$isAdmin,
                                'readonly' => !$isAdmin,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <?php if (!$isAdmin): ?>
                            <small class="form-text text-muted"><i class="fas fa-lock mr-1"></i><?= __('Only administrators can change the role.') ?></small>
                        <?php else: ?>
                            <small class="form-text text-muted"><i class="fas fa-info-circle mr-1"></i><?= __('Select the permission level for this user in the system.') ?></small>
                        <?php endif; ?>
                    </div>

                    <!-- Membros Associados -->
                    <div class="form-group">
                        <label class="font-weight-bold"><?= __('Linked Clan Members') ?></label>
                        <?= $this->Form->control('members._ids', [
                            'options' => $members,
                            'multiple' => true,
                            'label' => false,
                            'class' => 'form-control',
                            'style' => 'min-height: 180px;',
                            'templates' => ['inputContainer' => '{{content}}'],
                        ]) ?>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle mr-1"></i><?= __('Hold Ctrl (or Cmd) to select or deselect multiple members.') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between">
            <div class="mb-2 mb-sm-0">
                <?= $this->Form->postLink(
                    '<i class="fas fa-trash-alt mr-1"></i> ' . __('Delete'),
                    ['action' => 'delete', $user->id],
                    [
                        'confirm' => __('Are you sure you want to delete # {0}?', $user->id),
                        'class' => 'btn btn-danger',
                        'escape' => false,
                    ]
                ) ?>
            </div>
            <div class="d-flex" style="gap: 8px;">
                <?= $this->Html->link(
                    '<i class="fas fa-times mr-1"></i> ' . __('Cancel'),
                    ['action' => 'view', $user->id],
                    ['class' => 'btn btn-default', 'escape' => false]
                ) ?>
                <?= $this->Form->button(
                    '<i class="fas fa-save mr-1"></i> ' . __('Save'),
                    ['class' => 'btn btn-primary', 'escapeTitle' => false]
                ) ?>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>