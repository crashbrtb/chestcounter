<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\IncompleteChest> $incompleteChests
 * @var string $status
 * @var array<string, int> $counts
 */
?>

<?php
$this->assign('title', __('Incomplete Chests'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('Incomplete Chests')],
]);

$labels = [
    'pending' => ['text' => __('Pending'), 'class' => 'badge badge-warning'],
    'corrected' => ['text' => __('Corrected'), 'class' => 'badge badge-success'],
    'unresolved' => ['text' => __('Not recoverable'), 'class' => 'badge badge-secondary'],
];
$filters = [
    'pending' => __('Pending ({0})', $counts['pending'] ?? 0),
    'corrected' => __('Corrected ({0})', $counts['corrected'] ?? 0),
    'unresolved' => __('Not recoverable ({0})', $counts['unresolved'] ?? 0),
    'all' => __('All'),
];
?>

<div class="card card-primary card-outline">
    <div class="card-header d-flex flex-column flex-md-row">
        <h2 class="card-title">
            <?= __('Incomplete Chests') ?>
        </h2>
        <div class="d-flex ml-auto">
            <div class="btn-group btn-group-sm">
                <?php foreach ($filters as $key => $text) : ?>
                    <?= $this->Html->link($text, ['action' => 'index', '?' => ['status' => $key]], [
                        'class' => 'btn ' . ($status === $key ? 'btn-primary' : 'btn-outline-primary'),
                    ]) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
        <p class="text-muted">
            <?= __('Chests the collector opened but could not read. The screenshot of each one was kept so it can be filled in by hand — the chest itself is no longer in the game.') ?>
        </p>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('collected_at', __('Collected at')) ?></th>
                    <th><?= $this->Paginator->sort('name', __('Chest')) ?></th>
                    <th><?= $this->Paginator->sort('player', __('Player')) ?></th>
                    <th><?= $this->Paginator->sort('source', __('Source')) ?></th>
                    <th><?= $this->Paginator->sort('status', __('Status')) ?></th>
                    <th><?= $this->Paginator->sort('reviewed_at', __('Reviewed at')) ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $shown = 0; ?>
                <?php foreach ($incompleteChests as $incompleteChest) : ?>
                    <?php $shown++; ?>
                    <?php $label = $labels[$incompleteChest->status] ?? $labels['pending']; ?>
                    <tr>
                        <td><?= $this->Number->format($incompleteChest->id) ?></td>
                        <td><?= h($incompleteChest->collected_at) ?></td>
                        <td><?= $incompleteChest->name ? h($incompleteChest->name) : '<span class="text-muted">' . __('not read') . '</span>' ?></td>
                        <td><?= $incompleteChest->player ? h($incompleteChest->player) : '<span class="text-muted">' . __('not read') . '</span>' ?></td>
                        <td><?= $incompleteChest->source ? h($incompleteChest->source) : '<span class="text-muted">' . __('not read') . '</span>' ?></td>
                        <td><span class="<?= $label['class'] ?>"><?= $label['text'] ?></span></td>
                        <td><?= $incompleteChest->reviewed_at ? h($incompleteChest->reviewed_at) : '—' ?></td>
                        <td class="actions">
                            <?= $this->Html->link(
                                $incompleteChest->is_pending ? __('Review') : __('View'),
                                ['action' => 'view', $incompleteChest->id],
                                ['class' => 'btn btn-xs ' . ($incompleteChest->is_pending ? 'btn-primary' : 'btn-outline-primary')]
                            ) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($shown === 0) : ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted p-4">
                            <?= __('Nothing here — every chest was read.') ?>
                        </td>
                    </tr>
                <?php endif; ?>
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
