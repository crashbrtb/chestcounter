<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\StandardChest[]|\Cake\Collection\CollectionInterface $standardChests
 */
?>

<?php
$this->assign('title', __('Standard Chests'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Standard Chests')],
]);
?>

<div class="card card-primary card-outline">
    <div class="card-header d-flex flex-column flex-md-row">
        <h2 class="card-title">
            <!-- -->
        </h2>
        <div class="d-flex ml-auto">
            <?= $this->Paginator->limitControl([], null, [
                'label' => false,
                'class' => 'form-control form-control-sm',
                'templates' => ['inputContainer' => '{{content}}']
            ]); ?>
            <?= $this->Html->link(__('New Standard Chest'), ['action' => 'add'], ['class' => 'btn btn-primary btn-sm ml-2']) ?>
        </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('source', 'Source') ?></th>
                    <th><?= $this->Paginator->sort('score', 'Score') ?></th>
                    <th><?= $this->Paginator->sort('monster', 'Epic Monster') ?></th>
                    <th><?= $this->Paginator->sort('qty_chest', 'Chests Qty') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($standardChests as $standardChest): ?>
                <tr>
                    <td><?= h($standardChest->source) ?></td>
                    <td><?= $this->Number->format($standardChest->score) ?></td>
                    <td><?= $standardChest->monster ? __('Yes') : __('No') ?></td>
                    <td><?= $standardChest->qty_chest === null ? '' : $this->Number->format($standardChest->qty_chest) ?></td>
                    <td class="actions">
                        <?= $this->Html->link('<i class="fas fa-eye"></i>', ['action' => 'view', $standardChest->id], ['escape' => false, 'class' => 'btn btn-info btn-sm']) ?>
                        <?= $this->Html->link('<i class="fas fa-pencil-alt"></i>', ['action' => 'edit', $standardChest->id], ['escape' => false, 'class' => 'btn btn-warning btn-sm']) ?>
                        <?= $this->Form->postLink('<i class="fas fa-trash"></i>', ['action' => 'delete', $standardChest->id], ['escape' => false, 'class' => 'btn btn-danger btn-sm', 'confirm' => __('Are you sure you want to delete # {0}?', $standardChest->id)]) ?>
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