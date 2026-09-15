<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Config $config
 */

$this->assign('title', __('Edit Config'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Config'), 'url' => ['action' => 'index']],
    ['title' => __('View'), 'url' => ['action' => 'view', $config->id]],
    ['title' => __('Edit')],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-edit text-primary mr-2"></i><?= __('Edit Configuration') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Update parameter value or documentation') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-eye mr-1"></i> ' . __('View'),
                ['action' => 'view', $config->id],
                ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i> ' . __('List Configs'),
                ['action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <h3 class="card-title font-weight-bold mb-2 mb-md-0">
                <code class="text-primary font-weight-bold" style="font-size: 1.1rem;"><?= h($config->param) ?></code>
                <span class="text-muted small ml-1">#<?= $this->Number->format($config->id) ?></span>
            </h3>
        </div>

        <?= $this->Form->create($config) ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="param" class="font-weight-bold"><?= __('Parameter Key') ?></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                            </div>
                            <?= $this->Form->control('param', [
                                'label' => false,
                                'class' => 'form-control',
                                'disabled' => true,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <small class="form-text text-muted">
                            <?= __('Parameter names are immutable identifiers used throughout the codebase.') ?>
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
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="description-textarea" class="font-weight-bold"><?= __('Description & Purpose') ?></label>
                        <?= $this->Form->control('description', [
                            'type' => 'textarea',
                            'label' => false,
                            'class' => 'form-control',
                            'id' => 'description-textarea',
                            'rows' => 4,
                            'templates' => ['inputContainer' => '{{content}}'],
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between">
            <div class="mb-2 mb-sm-0">
                <?= $this->Form->postLink(
                    '<i class="fas fa-trash-alt mr-1"></i> ' . __('Delete'),
                    ['action' => 'delete', $config->id],
                    [
                        'confirm' => __('Are you sure you want to delete parameter "{0}"?', $config->param),
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

<script>
    const textarea = document.getElementById('description-textarea');
    if (textarea) {
        function autoResize() {
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight + 4) + 'px';
        }
        textarea.addEventListener('input', autoResize);
        window.addEventListener('load', autoResize);
    }
</script>