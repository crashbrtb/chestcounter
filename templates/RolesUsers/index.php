<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RolesUser[]|\Cake\Collection\CollectionInterface $rolesUsers
 */

$this->assign('title', __('Roles Users'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Roles Users')],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-user-shield text-primary mr-2"></i><?= __('User Role Assignments') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Direct mappings between system user accounts and roles') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-plus mr-1"></i> ' . __('Assign Role'),
                ['action' => 'add'],
                ['class' => 'btn btn-primary btn-sm', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-users mr-1"></i> ' . __('Users'),
                ['controller' => 'Users', 'action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-shield-alt mr-1"></i> ' . __('Roles'),
                ['controller' => 'Roles', 'action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <h3 class="card-title font-weight-bold mb-2 mb-md-0">
                <i class="fas fa-list text-primary mr-2"></i><?= __('Role Assignments') ?>
            </h3>
            <div class="d-flex align-items-center">
                <span class="mr-2 small text-muted"><?= __('Show:') ?></span>
                <?= $this->Paginator->limitControl([], null, [
                    'label' => false,
                    'class' => 'form-control form-control-sm',
                    'templates' => ['inputContainer' => '{{content}}']
                ]); ?>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th><?= $this->Paginator->sort('id', '#') ?></th>
                        <th><?= $this->Paginator->sort('user_id', __('User')) ?></th>
                        <th><?= $this->Paginator->sort('role_id', __('Role')) ?></th>
                        <th class="actions text-right"><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rolesUsers)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <?= __('No role assignments found.') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rolesUsers as $rolesUser): ?>
                            <tr>
                                <td>#<?= $this->Number->format($rolesUser->id) ?></td>
                                <td class="font-weight-bold">
                                    <?= $rolesUser->has('user') ? $this->Html->link('<i class="fas fa-user mr-1 text-primary"></i>' . h($rolesUser->user->name), ['controller' => 'Users', 'action' => 'view', $rolesUser->user->id], ['escape' => false]) : '<span class="text-muted">-</span>' ?>
                                </td>
                                <td>
                                    <?= $rolesUser->has('role') ? '<span class="badge badge-info"><i class="fas fa-shield-alt mr-1"></i>' . h($rolesUser->role->name) . '</span>' : '<span class="text-muted">-</span>' ?>
                                </td>
                                <td class="actions text-right">
                                    <?= $this->Html->link(
                                        '<i class="fas fa-eye"></i>',
                                        ['action' => 'view', $rolesUser->id],
                                        ['class' => 'btn btn-xs btn-outline-primary', 'escape' => false, 'title' => __('View')]
                                    ) ?>
                                    <?= $this->Html->link(
                                        '<i class="fas fa-edit"></i>',
                                        ['action' => 'edit', $rolesUser->id],
                                        ['class' => 'btn btn-xs btn-outline-primary', 'escape' => false, 'title' => __('Edit')]
                                    ) ?>
                                    <?= $this->Form->postLink(
                                        '<i class="fas fa-trash-alt"></i>',
                                        ['action' => 'delete', $rolesUser->id],
                                        [
                                            'class' => 'btn btn-xs btn-outline-danger',
                                            'escape' => false,
                                            'confirm' => __('Are you sure you want to delete # {0}?', $rolesUser->id),
                                            'title' => __('Delete')
                                        ]
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex flex-column flex-md-row align-items-center justify-content-between">
            <div class="text-muted small mb-2 mb-md-0">
                <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
            </div>
            <ul class="pagination pagination-sm mb-0">
                <?= $this->Paginator->first('<i class="fas fa-angle-double-left"></i>', ['escape' => false]) ?>
                <?= $this->Paginator->prev('<i class="fas fa-angle-left"></i>', ['escape' => false]) ?>
                <?= $this->Paginator->numbers() ?>
                <?= $this->Paginator->next('<i class="fas fa-angle-right"></i>', ['escape' => false]) ?>
                <?= $this->Paginator->last('<i class="fas fa-angle-double-right"></i>', ['escape' => false]) ?>
            </ul>
        </div>
    </div>
</div>