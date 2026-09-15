<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */

$this->assign('title', __('User: {0}', $user->name));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Users'), 'url' => ['action' => 'index']],
    ['title' => h($user->name)],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-id-badge text-primary mr-2"></i><?= h($user->name) ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('User profile, security permissions, and clan membership links') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-user-edit mr-1"></i> ' . __('Edit User'),
                ['action' => 'edit', $user->id],
                ['class' => 'btn btn-primary btn-sm', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i> ' . __('List Users'),
                ['action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <!-- Card Principal -->
    <div class="card card-primary card-outline mb-4">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <h3 class="card-title font-weight-bold mb-2 mb-md-0">
                <i class="fas fa-user-circle text-primary mr-2"></i><?= __('Account Details') ?>
                <span class="text-muted small ml-1">#<?= $this->Number->format($user->id) ?></span>
            </h3>
            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                <?php if ($user->active): ?>
                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> <?= __('Active') ?></span>
                <?php else: ?>
                    <span class="badge badge-warning text-dark"><i class="fas fa-clock mr-1"></i> <?= __('Pending / Inactive') ?></span>
                <?php endif; ?>

                <?php if (!empty($user->google_id)): ?>
                    <span class="badge badge-light border"><i class="fab fa-google text-danger mr-1"></i> <?= __('Linked with Google') ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <tbody>
                    <tr>
                        <th style="width: 250px;" class="bg-light"><?= __('Name') ?></th>
                        <td class="font-weight-bold"><?= h($user->name) ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Email') ?></th>
                        <td><?= h($user->email) ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Account Status') ?></th>
                        <td>
                            <?php if ($user->active): ?>
                                <span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> <?= __('Active (Access granted)') ?></span>
                            <?php else: ?>
                                <span class="text-warning font-weight-bold"><i class="fas fa-clock mr-1"></i> <?= __('Pending Approval') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Google Sign-In') ?></th>
                        <td>
                            <?php if (!empty($user->google_id)): ?>
                                <span class="badge badge-success"><i class="fab fa-google mr-1"></i> <?= __('Linked') ?></span>
                            <?php else: ?>
                                <span class="badge badge-secondary"><?= __('Not Linked') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Registered') ?></th>
                        <td><?= !empty($user->created) ? h($user->created->format('d/m/Y H:i:s')) : '-' ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Last Updated') ?></th>
                        <td><?= !empty($user->modified) ? h($user->modified->format('d/m/Y H:i:s')) : '-' ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between">
            <div class="mb-2 mb-sm-0 d-flex align-items-center" style="gap: 8px;">
                <?php if (!$user->active): ?>
                    <?= $this->Form->postLink(
                        '<i class="fas fa-check mr-1"></i> ' . __('Approve User'),
                        ['action' => 'toggleActive', $user->id],
                        ['class' => 'btn btn-success', 'escape' => false]
                    ) ?>
                <?php else: ?>
                    <?= $this->Form->postLink(
                        '<i class="fas fa-ban mr-1"></i> ' . __('Deactivate User'),
                        ['action' => 'toggleActive', $user->id],
                        [
                            'class' => 'btn btn-outline-secondary',
                            'escape' => false,
                            'confirm' => __('Are you sure you want to deactivate {0}?', $user->name),
                        ]
                    ) ?>
                <?php endif; ?>

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
                    '<i class="fas fa-arrow-left mr-1"></i> ' . __('Back to List'),
                    ['action' => 'index'],
                    ['class' => 'btn btn-default', 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-edit mr-1"></i> ' . __('Edit'),
                    ['action' => 'edit', $user->id],
                    ['class' => 'btn btn-primary', 'escape' => false]
                ) ?>
            </div>
        </div>
    </div>

    <!-- Membros Associados -->
    <div class="card card-outline card-info mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-users text-info mr-2"></i><?= __('Linked Clan Members') ?>
            </h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th><?= __('Player') ?></th>
                        <th><?= __('Power') ?></th>
                        <th><?= __('Active in Clan') ?></th>
                        <th class="actions text-right"><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($user->members)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                <?= __('No clan members linked to this user.') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($user->members as $member): ?>
                            <tr>
                                <td class="font-weight-bold"><?= h($member->player) ?></td>
                                <td><?= $this->Number->format($member->power) ?></td>
                                <td>
                                    <?= $member->active
                                        ? '<span class="badge badge-success">' . __('Yes') . '</span>'
                                        : '<span class="badge badge-secondary">' . __('No') . '</span>'
                                    ?>
                                </td>
                                <td class="actions text-right">
                                    <?= $this->Html->link(
                                        '<i class="fas fa-eye mr-1"></i> ' . __('View Member'),
                                        ['controller' => 'Members', 'action' => 'view', $member->id],
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

    <!-- Roles / Permissões -->
    <div class="card card-outline card-secondary">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-shield-alt text-secondary mr-2"></i><?= __('Assigned Roles') ?>
            </h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th><?= __('Role') ?></th>
                        <th><?= __('Description') ?></th>
                        <th><?= __('Alias') ?></th>
                        <th class="actions text-right"><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($user->roles)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                <?= __('No roles assigned.') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($user->roles as $role): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-info font-weight-bold">
                                        <i class="fas fa-user-tag mr-1"></i><?= h($role->name) ?>
                                    </span>
                                </td>
                                <td><?= h($role->description) ?></td>
                                <td><code><?= h($role->alias) ?></code></td>
                                <td class="actions text-right">
                                    <?= $this->Html->link(
                                        '<i class="fas fa-eye mr-1"></i> ' . __('View Role'),
                                        ['controller' => 'Roles', 'action' => 'view', $role->id],
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
