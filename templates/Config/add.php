<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Config $config
 */

$this->assign('title', __('Add Config'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Config'), 'url' => ['action' => 'index']],
    ['title' => __('Add')],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-plus-circle text-primary mr-2"></i><?= __('Add Configuration') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Create a new global configuration parameter and its default value') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i> ' . __('List Configs'),
                ['action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-cog text-primary mr-2"></i><?= __('New Parameter') ?>
            </h3>
        </div>

        <?= $this->Form->create($config, ['valueSources' => ['query', 'context']]) ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="param" class="font-weight-bold"><?= __('Parameter Key (Name)') ?> <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                            </div>
                            <?= $this->Form->control('param', [
                                'label' => false,
                                'class' => 'form-control',
                                'required' => true,
                                'placeholder' => __('e.g. system_feature_enabled'),
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <small class="form-text text-muted">
                            <?= __('Snake_case identifier used in code.') ?>
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="value" class="font-weight-bold"><?= __('Value') ?> <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-terminal"></i></span>
                            </div>
                            <?= $this->Form->control('value', [
                                'label' => false,
                                'class' => 'form-control',
                                'required' => true,
                                'placeholder' => __('Value (string or number)'),
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="description" class="font-weight-bold"><?= __('Description & Purpose') ?></label>
                        <?= $this->Form->control('description', [
                            'type' => 'textarea',
                            'label' => false,
                            'class' => 'form-control',
                            'rows' => 4,
                            'placeholder' => __('Explain what this configuration affects...'),
                            'templates' => ['inputContainer' => '{{content}}'],
                        ]) ?>
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
                '<i class="fas fa-save mr-1"></i> ' . __('Save Configuration'),
                ['class' => 'btn btn-primary', 'escapeTitle' => false]
            ) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>