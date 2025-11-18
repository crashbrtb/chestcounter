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

<div class="card card-primary card-outline">
    <?= $this->Form->create($user) ?>
    <div class="card-body">
        <?= $this->Form->control('name') ?>
        <?= $this->Form->control('email', [
            'disabled' => !$isAdmin,
            'readonly' => !$isAdmin
        ]) ?>
        <?php if (!$isAdmin): ?>
            <small class="form-text text-muted"><?= __('Only administrators can change the email.') ?></small>
        <?php endif; ?>
        <?= $this->Form->control('password') ?>
        <?= $this->Form->control('members._ids', ['options' => $members, 'multiple' => true]); ?>
        <?= $this->Form->control('roles._ids', [
            'options' => $roles,
            'disabled' => !$isAdmin,
            'readonly' => !$isAdmin
        ]) ?>
        <?php if (!$isAdmin): ?>
            <small class="form-text text-muted"><?= __('Only administrators can change the role.') ?></small>
        <?php endif; ?>
    </div>
    <div class="card-footer d-flex">
        <div class="mr-auto">
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $user->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $user->id), 'class' => 'btn btn-danger']
            ) ?>
        </div>
        <div class="ml-auto">
            <?= $this->Form->button(__('Save'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Html->link(__('Cancel'), ['action' => 'view', $user->id], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>