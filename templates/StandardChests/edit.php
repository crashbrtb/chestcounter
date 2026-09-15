<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\StandardChest $standardChest
 */

$this->assign('title', __('Edit Standard Chest'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Standard Chests'), 'url' => ['action' => 'index']],
    ['title' => __('View'), 'url' => ['action' => 'view', $standardChest->id]],
    ['title' => __('Edit')],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-edit text-primary mr-2"></i><?= __('Edit Standard Chest') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Modify chest configuration, scoring weight, and monster flags') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-eye mr-1"></i> ' . __('View'),
                ['action' => 'view', $standardChest->id],
                ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
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
                <i class="fas fa-box text-primary mr-2"></i><?= h($standardChest->source) ?>
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

        <?= $this->Form->create($standardChest) ?>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <!-- Source Name -->
                    <div class="form-group">
                        <label for="source" class="font-weight-bold"><?= __('Source Name') ?> <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            </div>
                            <?= $this->Form->control('source', [
                                'label' => false,
                                'class' => 'form-control',
                                'required' => true,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <small class="form-text text-muted">
                            <?= __('The raw name of the chest as scanned by OCR or reported by the game.') ?>
                        </small>
                    </div>

                    <!-- Friendly Alias -->
                    <div class="form-group">
                        <label for="alias" class="font-weight-bold"><?= __('Friendly Alias') ?></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-font"></i></span>
                            </div>
                            <?= $this->Form->control('alias', [
                                'label' => false,
                                'class' => 'form-control',
                                'required' => false,
                                'placeholder' => __('Optional friendly name for reports...'),
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <small class="form-text text-muted">
                            <?= __('Optional friendly name shown in reports instead of the raw source.') ?>
                        </small>
                    </div>
                </div>

                <div class="col-lg-6">
                    <!-- Score -->
                    <div class="form-group">
                        <label for="score" class="font-weight-bold"><?= __('Score / Points') ?> <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-star text-warning"></i></span>
                            </div>
                            <?= $this->Form->control('score', [
                                'label' => false,
                                'type' => 'number',
                                'class' => 'form-control',
                                'required' => true,
                                'min' => 0,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <small class="form-text text-muted">
                            <?= __('Points awarded for collecting this chest.') ?>
                        </small>
                    </div>

                    <!-- Monster classification & Qty -->
                    <div class="form-group p-3 rounded bg-light border">
                        <label class="font-weight-bold d-block mb-2"><?= __('Epic Monster Classification') ?></label>
                        <?= $this->Form->control('monster', [
                            'type' => 'checkbox',
                            'custom' => true,
                            'label' => __('Epic Monster Chest (Drop from killing an Epic Monster)'),
                        ]) ?>

                        <div class="mt-3">
                            <label for="qty-chest" class="font-weight-bold"><?= __('Chests Quantity per Monster') ?></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                                </div>
                                <?= $this->Form->control('qty_chest', [
                                    'label' => false,
                                    'type' => 'number',
                                    'class' => 'form-control',
                                    'min' => 1,
                                    'templates' => ['inputContainer' => '{{content}}'],
                                ]) ?>
                            </div>
                            <small class="form-text text-muted">
                                <?= __('If chest type is Epic Monster, inform how many chests are earned by killing one monster.') ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
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
                    '<i class="fas fa-times mr-1"></i> ' . __('Cancel'),
                    ['action' => 'index'],
                    ['class' => 'btn btn-default', 'escape' => false]
                ) ?>
                <?= $this->Form->button(
                    '<i class="fas fa-save mr-1"></i> ' . __('Save Changes'),
                    ['class' => 'btn btn-primary', 'escapeTitle' => false]
                ) ?>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>