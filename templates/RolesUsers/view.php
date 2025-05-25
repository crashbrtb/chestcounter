<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RolesUser $rolesUser
 */
?>

<?php
$this->assign('title', __('Roles User'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Roles Users'), 'url' => ['action' => 'index']],
    ['title' => __('View')],
]);
?>

<div class="view card card-primary card-outline">
    <div class="card-header d-sm-flex">
        <h2 class="card-title"><?= h($rolesUser->id) ?></h2>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <tr>
                <th><?= __('User') ?></th>
                <td><?= $rolesUser->has('user') ? $this->Html->link($rolesUser->user->name, ['controller' => 'Users', 'action' => 'view', $rolesUser->user->id]) : '' ?></td>
            </tr>
            <tr>
                <th><?= __('Role') ?></th>
                <td><?= $rolesUser->has('role') ? $this->Html->link($rolesUser->role->name, ['controller' => 'Roles', 'action' => 'view', $rolesUser->role->id]) : '' ?></td>
            </tr>
            <tr>
                <th><?= __('Id') ?></th>
                <td><?= $this->Number->format($rolesUser->id) ?></td>
            </tr>
        </table>
    </div>
    <div class="card-footer d-flex">
        <div class="mr-auto">
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $rolesUser->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $rolesUser->id), 'class' => 'btn btn-danger']
            ) ?>
        </div>
        <div class="ml-auto">
            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $rolesUser->id], ['class' => 'btn btn-secondary']) ?>
            <?= $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
</div>
