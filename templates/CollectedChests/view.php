<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CollectedChest $collectedChest
 */
?>

<?php
$this->assign('title', __('Collected Chest'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Collected Chests'), 'url' => ['action' => 'index']],
    ['title' => __('View')],
]);
?>

<div class="view card card-primary card-outline">
    <div class="card-header d-sm-flex">
        <h2 class="card-title"><?= h($collectedChest->name) ?></h2>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <tr>
                <th><?= __('Name') ?></th>
                <td><?= h($collectedChest->name) ?></td>
            </tr>
            <tr>
                <th><?= __('Player') ?></th>
                <td><?= h($collectedChest->player) ?></td>
            </tr>
            <tr>
                <th><?= __('Source') ?></th>
                <td><?= h($collectedChest->source) ?></td>
            </tr>
            <tr>
                <th><?= __('Id') ?></th>
                <td><?= $this->Number->format($collectedChest->id) ?></td>
            </tr>
            <tr>
                <th><?= __('Type') ?></th>
                <td><?= $this->Number->format($collectedChest->type) ?></td>
            </tr>
            <tr>
                <th><?= __('Collected At') ?></th>
                <td><?= h($collectedChest->collected_at) ?></td>
            </tr>
        </table>
    </div>
    <div class="card-footer d-flex">
        <div class="mr-auto">
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $collectedChest->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $collectedChest->id), 'class' => 'btn btn-danger']
            ) ?>
        </div>
        <div class="ml-auto">
            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $collectedChest->id], ['class' => 'btn btn-secondary']) ?>
            <?= $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
</div>
