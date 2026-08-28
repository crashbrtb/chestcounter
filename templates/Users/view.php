<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>

<?php
$this->assign('title', __('User'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Users'), 'url' => ['action' => 'index']],
    ['title' => __('View')],
]);
?>

<div class="view card card-primary card-outline">
    <div class="card-header d-sm-flex">
        <h2 class="card-title"><?= h($user->name) ?></h2>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <tr>
                <th><?= __('Name') ?></th>
                <td><?= h($user->name) ?></td>
            </tr>
            <tr>
                <th><?= __('Email') ?></th>
                <td><?= h($user->email) ?></td>
            </tr>
            <tr>
                <th><?= __('Status') ?></th>
                <td>
                    <?php if ($user->active): ?>
                        <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> <?= __('Active') ?></span>
                    <?php else: ?>
                        <span class="badge badge-warning text-dark"><i class="fas fa-clock mr-1"></i> <?= __('Pending / Inactive') ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><?= __('Google Account') ?></th>
                <td>
                    <?php if (!empty($user->google_id)): ?>
                        <span class="badge badge-success"><i class="fab fa-google mr-1"></i> <?= __('Linked') ?></span>
                    <?php else: ?>
                        <span class="badge badge-secondary"><?= __('Not Linked') ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><?= __('Id') ?></th>
                <td><?= $this->Number->format($user->id) ?></td>
            </tr>
            <tr>
                <th><?= __('Created') ?></th>
                <td><?= h($user->created) ?></td>
            </tr>
            <tr>
                <th><?= __('Modified') ?></th>
                <td><?= h($user->modified) ?></td>
            </tr>
        </table>
    </div>
    <div class="card-footer d-flex">
        <div class="mr-auto">
            <?php if (!$user->active): ?>
                <?= $this->Form->postLink('<i class="fas fa-check mr-1"></i> ' . __('Approve User'), ['action' => 'toggleActive', $user->id], ['class' => 'btn btn-success mr-2', 'escape' => false]) ?>
            <?php else: ?>
                <?= $this->Form->postLink('<i class="fas fa-ban mr-1"></i> ' . __('Deactivate User'), ['action' => 'toggleActive', $user->id], ['class' => 'btn btn-outline-secondary mr-2', 'escape' => false, 'confirm' => __('Are you sure you want to deactivate {0}?', $user->name)]) ?>
            <?php endif; ?>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $user->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $user->id), 'class' => 'btn btn-danger']
            ) ?>
        </div>
        <div class="ml-auto">
            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $user->id], ['class' => 'btn btn-primary']) ?>
            <?= $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
</div>

<div class="related related-members view card">
    <div class="card-header d-flex">
        <h3 class="card-title"><?= __('Related Members') ?></h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <tr>
                <th><?= __('Player') ?></th>
                <th><?= __('Power') ?></th>
                <th><?= __('Active') ?></th>
                <th class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php if (empty($user->members)) : ?>
                <tr>
                    <td colspan="4" class="text-muted">
                        <?= __('Members record not found!') ?>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ($user->members as $member) : ?>
                    <tr>
                        <td><?= h($member->player) ?></td>
                        <td><?= h($member->power) ?></td>
                        <td><?= $member->active ? __('Yes') : __('No'); ?></td>
                        <td class="actions">
                            <?= $this->Html->link(__('View'), ['controller' => 'Members', 'action' => 'view', $member->id], ['class' => 'btn btn-xs btn-outline-primary']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
</div>

<div class="related related-role view card">
    <div class="card-header d-flex">
        <h3 class="card-title"><?= __('Related Roles') ?></h3>
        <div class="ml-auto">
            <?= $this->Html->link(__('New Role'), ['controller' => 'Roles', 'action' => 'add', '?' => ['user_id' => $user->id]], ['class' => 'btn btn-primary btn-sm']) ?>
            <?= $this->Html->link(__('List Roles'), ['controller' => 'Roles', 'action' => 'index'], ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <tr>
                <th><?= __('Id') ?></th>
                <th><?= __('Name') ?></th>
                <th><?= __('Description') ?></th>
                <th><?= __('Alias') ?></th>
                <th><?= __('Created') ?></th>
                <th><?= __('Modified') ?></th>
                <th class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php if (empty($user->roles)) : ?>
                <tr>
                    <td colspan="7" class="text-muted">
                        <?= __('Roles record not found!') ?>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ($user->roles as $role) : ?>
                    <tr>
                        <td><?= h($role->id) ?></td>
                        <td><?= h($role->name) ?></td>
                        <td><?= h($role->description) ?></td>
                        <td><?= h($role->alias) ?></td>
                        <td><?= h($role->created) ?></td>
                        <td><?= h($role->modified) ?></td>
                        <td class="actions">
                            <?= $this->Html->link(__('View'), ['controller' => 'Roles', 'action' => 'view', $role->id], ['class' => 'btn btn-xs btn-outline-primary']) ?>
                            <?= $this->Html->link(__('Edit'), ['controller' => 'Roles', 'action' => 'edit', $role->id], ['class' => 'btn btn-xs btn-outline-primary']) ?>
                            <?= $this->Form->postLink(__('Delete'), ['controller' => 'Roles', 'action' => 'delete', $role->id], ['class' => 'btn btn-xs btn-outline-danger', 'confirm' => __('Are you sure you want to delete # {0}?', $role->id)]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
</div>
