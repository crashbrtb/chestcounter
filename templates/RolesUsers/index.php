<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\RolesUser> $rolesUsers
 */
?>
<div class="rolesUsers index content">
    <?= $this->Html->link(__('New Roles User'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Roles Users') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('role_id') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rolesUsers as $rolesUser): ?>
                <tr>
                    <td><?= $this->Number->format($rolesUser->id) ?></td>
                    <td><?= $rolesUser->hasValue('user') ? $this->Html->link($rolesUser->user->name, ['controller' => 'Users', 'action' => 'view', $rolesUser->user->id]) : '' ?></td>
                    <td><?= $rolesUser->hasValue('role') ? $this->Html->link($rolesUser->role->name, ['controller' => 'Roles', 'action' => 'view', $rolesUser->role->id]) : '' ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $rolesUser->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $rolesUser->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $rolesUser->id], ['confirm' => __('Are you sure you want to delete # {0}?', $rolesUser->id)]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>