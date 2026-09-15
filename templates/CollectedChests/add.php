<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CollectedChest $collectedChest
 */

$this->assign('title', __('Add Collected Chest'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Collected Chests'), 'url' => ['action' => 'index']],
    ['title' => __('Add')],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-plus-circle text-primary mr-2"></i><?= __('Add Collected Chest') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Manually register a chest collection entry') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i> ' . __('List Collected Chests'),
                ['action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-box text-primary mr-2"></i><?= __('Collection Record') ?>
            </h3>
        </div>

        <?= $this->Form->create($collectedChest, ['valueSources' => ['query', 'context']]) ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name" class="font-weight-bold"><?= __('Chest Name') ?> <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-box-open"></i></span>
                            </div>
                            <?= $this->Form->control('name', [
                                'label' => false,
                                'class' => 'form-control',
                                'required' => true,
                                'placeholder' => __('e.g. Chest of the Ancients'),
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="player" class="font-weight-bold"><?= __('Player Name') ?> <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <?= $this->Form->control('player', [
                                'label' => false,
                                'class' => 'form-control',
                                'required' => true,
                                'placeholder' => __('Player name'),
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="source" class="font-weight-bold"><?= __('Source') ?></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            </div>
                            <?= $this->Form->control('source', [
                                'label' => false,
                                'class' => 'form-control',
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="type" class="font-weight-bold"><?= __('Chest Type') ?></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-list-ol"></i></span>
                            </div>
                            <?= $this->Form->control('type', [
                                'label' => false,
                                'type' => 'number',
                                'class' => 'form-control',
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-end" style="gap: 8px;">
            <?= $this->Html->link(
                '<i class="fas fa-times mr-1"></i> ' . __('Cancel'),
                ['action' => 'index'],
                ['class' => 'btn btn-default', 'escape' => false]
            ) ?>
            <?= $this->Form->button(
                '<i class="fas fa-save mr-1"></i> ' . __('Save Record'),
                ['class' => 'btn btn-primary', 'escapeTitle' => false]
            ) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>