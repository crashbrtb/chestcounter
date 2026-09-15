<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\PlayerNameMapping $playerNameMapping
 */

$this->assign('title', __('Edit Player Name Mapping'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Player Name Mappings'), 'url' => ['action' => 'index']],
    ['title' => __('View'), 'url' => ['action' => 'view', $playerNameMapping->id]],
    ['title' => __('Edit')],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-edit text-primary mr-2"></i><?= __('Edit Player Name Mapping') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Update OCR matching pattern and destination clan player name') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-eye mr-1"></i> ' . __('View'),
                ['action' => 'view', $playerNameMapping->id],
                ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i> ' . __('List Mappings'),
                ['action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <h3 class="card-title font-weight-bold mb-2 mb-md-0">
                <i class="fas fa-spell-check text-primary mr-2"></i><?= h($playerNameMapping->ocr_text) ?> &rarr; <?= h($playerNameMapping->correct_name) ?>
                <span class="text-muted small ml-1">#<?= $this->Number->format($playerNameMapping->id) ?></span>
            </h3>
        </div>

        <?= $this->Form->create($playerNameMapping) ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="ocr-text" class="font-weight-bold"><?= __('Scanned OCR Text') ?> <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-camera"></i></span>
                            </div>
                            <?= $this->Form->control('ocr_text', [
                                'label' => false,
                                'class' => 'form-control',
                                'required' => true,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <small class="form-text text-muted">
                            <?= __('The text that OCR misidentifies from screenshots.') ?>
                        </small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="correct-name" class="font-weight-bold"><?= __('Correct Member Name') ?> <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user-check"></i></span>
                            </div>
                            <?= $this->Form->control('correct_name', [
                                'label' => false,
                                'class' => 'form-control',
                                'required' => true,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <small class="form-text text-muted">
                            <?= __('The destination member name in the database.') ?>
                        </small>
                    </div>
                </div>

                <div class="col-12 mt-2 pt-2 border-top d-flex justify-content-between text-muted small">
                    <span><i class="fas fa-clock mr-1"></i><?= __('Created: {0}', !empty($playerNameMapping->created) ? $playerNameMapping->created->format('d/m/Y H:i') : '-') ?></span>
                    <span><i class="fas fa-history mr-1"></i><?= __('Modified: {0}', !empty($playerNameMapping->modified) ? $playerNameMapping->modified->format('d/m/Y H:i') : '-') ?></span>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between">
            <div class="mb-2 mb-sm-0">
                <?= $this->Form->postLink(
                    '<i class="fas fa-trash-alt mr-1"></i> ' . __('Delete'),
                    ['action' => 'delete', $playerNameMapping->id],
                    [
                        'confirm' => __('Are you sure you want to delete # {0}?', $playerNameMapping->id),
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
