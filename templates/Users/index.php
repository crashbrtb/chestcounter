<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User[]|\Cake\Collection\CollectionInterface $users
 */
?>

<?php
$this->assign('title', __('Users'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Users')],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-users-cog text-primary mr-2"></i><?= __('Users') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Manage registered user accounts, permissions, and clan member associations') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-user-plus mr-1"></i> ' . __('New User'),
                ['action' => 'add'],
                ['class' => 'btn btn-primary btn-sm', 'escape' => false]
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
                <i class="fas fa-id-card text-primary mr-2"></i><?= __('System Users') ?>
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
    <!-- /.card-header -->
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('email') ?></th>
                    <th><?= $this->Paginator->sort('active', __('Status')) ?></th>
                    <th><?= __('Role') ?></th>
                    <th><?= $this->Paginator->sort('member_id', 'Member') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : ?>
                    <tr class="<?= !$user->active ? 'table-warning' : '' ?>">
                        <td><?= $this->Number->format($user->id) ?></td>
                        <td>
                            <?= h($user->name) ?>
                            <?php if (!empty($user->google_id)): ?>
                                <i class="fab fa-google text-danger ml-1" title="<?= __('Linked with Google') ?>"></i>
                            <?php endif; ?>
                        </td>
                        <td><?= h($user->email) ?></td>
                        <td>
                            <?php if ($user->active): ?>
                                <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> <?= __('Active') ?></span>
                            <?php else: ?>
                                <span class="badge badge-warning text-dark"><i class="fas fa-clock mr-1"></i> <?= __('Pending') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($user->roles)) : ?>
                                <?php foreach ($user->roles as $role) : ?>
                                    <?php
                                    $roleName = strtolower((string)$role->name);
                                    $badgeClass = match ($roleName) {
                                        'admin' => 'badge-danger',
                                        'bankers' => 'badge-info',
                                        default => 'badge-secondary',
                                    };
                                    $iconClass = match ($roleName) {
                                        'admin' => 'fas fa-user-shield',
                                        'bankers' => 'fas fa-piggy-bank',
                                        default => 'fas fa-user',
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?> mr-1">
                                        <i class="<?= $iconClass ?> mr-1"></i><?= h($role->name) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($user->members)) : ?>
                                <?= implode(', ', collection($user->members)->extract('player')->toList()) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= h($user->created) ?></td>
                        <td class="actions">
                            <?php if (!$user->active): ?>
                                <?= $this->Form->postLink('<i class="fas fa-check"></i> ' . __('Approve'), ['action' => 'toggleActive', $user->id], ['class' => 'btn btn-xs btn-success font-weight-bold', 'escape' => false, 'title' => __('Approve user access')]) ?>
                            <?php else: ?>
                                <?= $this->Form->postLink('<i class="fas fa-ban"></i>', ['action' => 'toggleActive', $user->id], ['class' => 'btn btn-xs btn-outline-secondary', 'escape' => false, 'confirm' => __('Are you sure you want to deactivate {0}?', $user->name), 'title' => __('Deactivate user')]) ?>
                            <?php endif; ?>
                            <?= $this->Html->link(__('View'), ['action' => 'view', $user->id], ['class' => 'btn btn-xs btn-outline-primary', 'escape' => false]) ?>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $user->id], ['class' => 'btn btn-xs btn-outline-primary', 'escape' => false]) ?>
                            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $user->id], ['class' => 'btn btn-xs btn-outline-danger', 'escape' => false, 'confirm' => __('Are you sure you want to delete # {0}?', $user->id)]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- /.card-body -->
    <div class="card-footer d-flex flex-column flex-md-row">
        <div class="text-muted">
            <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
        </div>
        <ul class="pagination pagination-sm mb-0 ml-auto">
            <?= $this->Paginator->first('<i class="fas fa-angle-double-left"></i>', ['escape' => false]) ?>
            <?= $this->Paginator->prev('<i class="fas fa-angle-left"></i>', ['escape' => false]) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next('<i class="fas fa-angle-right"></i>', ['escape' => false]) ?>
            <?= $this->Paginator->last('<i class="fas fa-angle-double-right"></i>', ['escape' => false]) ?>
        </ul>
    </div>
    <!-- /.card-footer -->
    </div>
</div>