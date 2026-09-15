<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RolesUser $rolesUser
 */

$this->assign('title', __('Roles User # {0}', $rolesUser->id));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Roles Users'), 'url' => ['action' => 'index']],
    ['title' => __('View')],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-user-shield text-primary mr-2"></i><?= __('Role Assignment # {0}', $this->Number->format($rolesUser->id)) ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Direct user-to-role permission linkage') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-edit mr-1"></i> ' . __('Edit'),
                ['action' => 'edit', $rolesUser->id],
                ['class' => 'btn btn-primary btn-sm', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i> ' . __('List Assignments'),
                ['action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <h3 class="card-title font-weight-bold mb-2 mb-md-0">
                <i class="fas fa-info-circle text-primary mr-2"></i><?= __('Assignment Information') ?>
            </h3>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <tbody>
                    <tr>
                        <th style="width: 250px;" class="bg-light"><?= __('User') ?></th>
                        <td><?= $rolesUser->has('user') ? $this->Html->link('<i class="fas fa-user mr-1 text-primary"></i>' . h($rolesUser->user->name), ['controller' => 'Users', 'action' => 'view', $rolesUser->user->id], ['escape' => false]) : '<span class="text-muted">-</span>' ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Role') ?></th>
                        <td><?= $rolesUser->has('role') ? '<span class="badge badge-info"><i class="fas fa-shield-alt mr-1"></i>' . h($rolesUser->role->name) . '</span>' : '<span class="text-muted">-</span>' ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Assignment ID') ?></th>
                        <td>#<?= $this->Number->format($rolesUser->id) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between">
            <div class="mb-2 mb-sm-0">
                <?= $this->Form->postLink(
                    '<i class="fas fa-trash-alt mr-1"></i> ' . __('Delete'),
                    ['action' => 'delete', $rolesUser->id],
                    [
                        'confirm' => __('Are you sure you want to delete # {0}?', $rolesUser->id),
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
                    ['action' => 'edit', $rolesUser->id],
                    ['class' => 'btn btn-primary', 'escape' => false]
                ) ?>
            </div>
        </div>
    </div>
</div>
