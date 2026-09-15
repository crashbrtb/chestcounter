<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\StandardChest $standardChest
 */

$this->assign('title', __('Standard Chest: {0}', $standardChest->source));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Standard Chests'), 'url' => ['action' => 'index']],
    ['title' => h($standardChest->source)],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-box text-primary mr-2"></i><?= h($standardChest->source) ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Standard chest specification, score value, and monster drop configuration') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-edit mr-1"></i> ' . __('Edit'),
                ['action' => 'edit', $standardChest->id],
                ['class' => 'btn btn-primary btn-sm', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i> ' . __('List Standard Chests'),
                ['action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <h3 class="card-title font-weight-bold mb-2 mb-md-0">
                <i class="fas fa-info-circle text-primary mr-2"></i><?= __('Chest Attributes') ?>
                <span class="text-muted small ml-1">#<?= $this->Number->format($standardChest->id) ?></span>
            </h3>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <?php if ($standardChest->monster): ?>
                    <span class="badge badge-danger"><i class="fas fa-dragon mr-1"></i><?= __('Epic Monster') ?></span>
                <?php else: ?>
                    <span class="badge badge-info"><i class="fas fa-box mr-1"></i><?= __('Regular Chest') ?></span>
                <?php endif; ?>
                <span class="badge badge-light border"><i class="fas fa-star text-warning mr-1"></i><?= $this->Number->format($standardChest->score) ?> pts</span>
            </div>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <tbody>
                    <tr>
                        <th style="width: 250px;" class="bg-light"><?= __('Source (OCR / Game Name)') ?></th>
                        <td class="font-weight-bold"><?= h($standardChest->source) ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Friendly Alias') ?></th>
                        <td><?= $standardChest->alias ? '<span class="font-weight-bold text-primary">' . h($standardChest->alias) . '</span>' : '<span class="text-muted">&mdash;</span>' ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Internal ID') ?></th>
                        <td>#<?= $this->Number->format($standardChest->id) ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Score Value') ?></th>
                        <td>
                            <span class="badge badge-warning text-dark font-weight-bold px-2 py-1">
                                <i class="fas fa-star mr-1"></i><?= $this->Number->format($standardChest->score) ?> <?= __('points') ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Classification') ?></th>
                        <td>
                            <?php if ($standardChest->monster): ?>
                                <span class="text-danger font-weight-bold"><i class="fas fa-dragon mr-1"></i><?= __('Epic Monster Chest') ?></span>
                            <?php else: ?>
                                <span class="text-info font-weight-bold"><i class="fas fa-box mr-1"></i><?= __('Regular Chest') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Chests Quantity per Monster') ?></th>
                        <td><?= $standardChest->qty_chest !== null ? $this->Number->format($standardChest->qty_chest) : '<span class="text-muted">&mdash;</span>' ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between">
            <div class="mb-2 mb-sm-0">
                <?= $this->Form->postLink(
                    '<i class="fas fa-trash-alt mr-1"></i> ' . __('Delete'),
                    ['action' => 'delete', $standardChest->id],
                    [
                        'confirm' => __('Are you sure you want to delete # {0}?', $standardChest->id),
                        'class' => 'btn btn-danger',
                        'escape' => false,
                    ]
                ) ?>
            </div>
            <div class="d-flex" style="gap: 8px;">
                <?= $this->Html->link(
                    '<i class="fas fa-arrow-left mr-1"></i> ' . __('Back to List'),
                    ['action' => 'index'],
                    ['class' => 'btn btn-default', 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-edit mr-1"></i> ' . __('Edit'),
                    ['action' => 'edit', $standardChest->id],
                    ['class' => 'btn btn-primary', 'escape' => false]
                ) ?>
            </div>
        </div>
    </div>
</div>
