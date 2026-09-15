<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Role $role
 */

$this->assign('title', __('Role: {0}', $role->name));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Roles'), 'url' => ['action' => 'index']],
    ['title' => h($role->name)],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-shield-alt text-primary mr-2"></i><?= h($role->name) ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Role details and assigned system users') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-edit mr-1"></i> ' . __('Edit'),
                ['action' => 'edit', $role->id],
                ['class' => 'btn btn-primary btn-sm', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i> ' . __('List Roles'),
                ['action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="card card-primary card-outline mb-4">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <h3 class="card-title font-weight-bold mb-2 mb-md-0">
                <i class="fas fa-info-circle text-primary mr-2"></i><?= __('Role Information') ?>
                <span class="text-muted small ml-1">#<?= $this->Number->format($role->id) ?></span>
            </h3>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <tbody>
                    <tr>
                        <th style="width: 250px;" class="bg-light"><?= __('Name') ?></th>
                        <td>
                            <span class="badge badge-info font-weight-bold px-2 py-1">
                                <i class="fas fa-shield-alt mr-1"></i><?= h($role->name) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Alias') ?></th>
                        <td><code><?= h($role->alias) ?></code></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Description') ?></th>
                        <td><?= h($role->description) ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Created') ?></th>
                        <td><?= !empty($role->created) ? h($role->created->format('d/m/Y H:i:s')) : '-' ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Modified') ?></th>
                        <td><?= !empty($role->modified) ? h($role->modified->format('d/m/Y H:i:s')) : '-' ?></td>
                    </tr>
                </tbody>
            </table>
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
                    '<i class="fas fa-arrow-left mr-1"></i> ' . __('Back to List'),
                    ['action' => 'index'],
                    ['class' => 'btn btn-default', 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-edit mr-1"></i> ' . __('Edit'),
                    ['action' => 'edit', $role->id],
                    ['class' => 'btn btn-primary', 'escape' => false]
                ) ?>
            </div>
        </div>
    </div>

    <!-- Usuários com esta Role -->
    <div class="card card-outline card-secondary">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-users text-secondary mr-2"></i><?= __('Users Assigned to this Role') ?>
            </h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th><?= __('Name') ?></th>
                        <th><?= __('Email') ?></th>
                        <th><?= __('Status') ?></th>
                        <th class="actions text-right"><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($role->users)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                <?= __('No users currently assigned to this role.') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($role->users as $user): ?>
                            <tr>
                                <td class="font-weight-bold"><?= h($user->name) ?></td>
                                <td><?= h($user->email) ?></td>
                                <td>
                                    <?= $user->active
                                        ? '<span class="badge badge-success">' . __('Active') . '</span>'
                                        : '<span class="badge badge-secondary">' . __('Pending') . '</span>'
                                    ?>
                                </td>
                                <td class="actions text-right">
                                    <?= $this->Html->link(
                                        '<i class="fas fa-eye mr-1"></i> ' . __('View User'),
                                        ['controller' => 'Users', 'action' => 'view', $user->id],
                                        ['class' => 'btn btn-xs btn-outline-primary', 'escape' => false]
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
