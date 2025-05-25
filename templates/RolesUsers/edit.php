<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RolesUser $rolesUser
 */
?>

<?php
$this->assign('title', __('Edit Roles User'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Roles Users'), 'url' => ['action' => 'index']],
    ['title' => __('View'), 'url' => ['action' => 'view', $rolesUser->id]],
    ['title' => __('Edit')],
]);
?>

<div class="card card-primary card-outline">
    <?= $this->Form->create($rolesUser) ?>
    <div class="card-body">
        <?= $this->Form->control('user_id', ['options' => $users, 'empty' => true, 'class' => 'form-control']) ?>
        <?= $this->Form->control('role_id', ['options' => $roles, 'empty' => true, 'class' => 'form-control']) ?>
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
            <?= $this->Form->button(__('Save'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Html->link(__('Cancel'), ['action' => 'view', $rolesUser->id], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>