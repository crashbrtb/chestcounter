<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CollectedChest $collectedChest
 */

$this->assign('title', __('Collected Chest: {0}', $collectedChest->name));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Collected Chests'), 'url' => ['action' => 'index']],
    ['title' => h($collectedChest->name)],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-box text-primary mr-2"></i><?= h($collectedChest->name) ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Chest collection event details and player log') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-edit mr-1"></i> ' . __('Edit'),
                ['action' => 'edit', $collectedChest->id],
                ['class' => 'btn btn-primary btn-sm', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i> ' . __('List Collected Chests'),
                ['action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <h3 class="card-title font-weight-bold mb-2 mb-md-0">
                <i class="fas fa-info-circle text-primary mr-2"></i><?= __('Collection Event Details') ?>
                <span class="text-muted small ml-1">#<?= $this->Number->format($collectedChest->id) ?></span>
            </h3>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <tbody>
                    <tr>
                        <th style="width: 250px;" class="bg-light"><?= __('Chest Name') ?></th>
                        <td class="font-weight-bold"><?= h($collectedChest->name) ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Player') ?></th>
                        <td><span class="badge badge-light border font-weight-bold"><i class="fas fa-user mr-1 text-primary"></i><?= h($collectedChest->player) ?></span></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Source') ?></th>
                        <td><?= h($collectedChest->source) ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Type') ?></th>
                        <td><span class="badge badge-secondary"><?= $this->Number->format($collectedChest->type) ?></span></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Collected At') ?></th>
                        <td><?= !empty($collectedChest->collected_at) ? h($collectedChest->collected_at->format('d/m/Y H:i:s')) : '-' ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Log ID') ?></th>
                        <td>#<?= $this->Number->format($collectedChest->id) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between">
            <div class="mb-2 mb-sm-0">
                <?= $this->Form->postLink(
                    '<i class="fas fa-trash-alt mr-1"></i> ' . __('Delete'),
                    ['action' => 'delete', $collectedChest->id],
                    [
                        'confirm' => __('Are you sure you want to delete # {0}?', $collectedChest->id),
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
                    ['action' => 'edit', $collectedChest->id],
                    ['class' => 'btn btn-primary', 'escape' => false]
                ) ?>
            </div>
        </div>
    </div>
</div>
