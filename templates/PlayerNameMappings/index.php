<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\PlayerNameMapping> $playerNameMappings
 */

$this->assign('title', __('Player Name Mappings'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Player Name Mappings')],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-exchange-alt text-primary mr-2"></i><?= __('Player Name Mappings') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Map OCR-recognized scanned names to exact clan member names') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-plus mr-1"></i> ' . __('New Mapping'),
                ['action' => 'add'],
                ['class' => 'btn btn-primary btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <h3 class="card-title font-weight-bold mb-2 mb-md-0">
                <i class="fas fa-spell-check text-primary mr-2"></i><?= __('OCR Correction Rules') ?>
            </h3>
            <div class="d-flex align-items-center">
                <span class="mr-2 small text-muted"><?= __('Show:') ?></span>
                <?= $this->Paginator->limitControl([], null, [
                    'label' => false,
                    'class' => 'form-control form-control-sm',
                    'templates' => ['inputContainer' => '{{content}}']
                ]); ?>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th><?= $this->Paginator->sort('id', '#') ?></th>
                        <th><?= $this->Paginator->sort('ocr_text', __('Scanned OCR Text')) ?></th>
                        <th><?= $this->Paginator->sort('correct_name', __('Correct Member Name')) ?></th>
                        <th><?= $this->Paginator->sort('created', __('Created')) ?></th>
                        <th><?= $this->Paginator->sort('modified', __('Modified')) ?></th>
                        <th class="actions text-right"><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($playerNameMappings)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                <?= __('No name mappings configured.') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($playerNameMappings as $playerNameMapping): ?>
                            <tr>
                                <td>#<?= $this->Number->format($playerNameMapping->id) ?></td>
                                <td><code class="text-danger font-weight-bold"><?= h($playerNameMapping->ocr_text) ?></code></td>
                                <td><span class="badge badge-success font-weight-bold p-1 px-2"><i class="fas fa-user mr-1"></i><?= h($playerNameMapping->correct_name) ?></span></td>
                                <td><?= !empty($playerNameMapping->created) ? h($playerNameMapping->created->format('d/m/Y H:i')) : '-' ?></td>
                                <td><?= !empty($playerNameMapping->modified) ? h($playerNameMapping->modified->format('d/m/Y H:i')) : '-' ?></td>
                                <td class="actions text-right">
                                    <?= $this->Html->link(
                                        '<i class="fas fa-eye"></i>',
                                        ['action' => 'view', $playerNameMapping->id],
                                        ['class' => 'btn btn-xs btn-outline-primary', 'escape' => false, 'title' => __('View')]
                                    ) ?>
                                    <?= $this->Html->link(
                                        '<i class="fas fa-edit"></i>',
                                        ['action' => 'edit', $playerNameMapping->id],
                                        ['class' => 'btn btn-xs btn-outline-primary', 'escape' => false, 'title' => __('Edit')]
                                    ) ?>
                                    <?= $this->Form->postLink(
                                        '<i class="fas fa-trash-alt"></i>',
                                        ['action' => 'delete', $playerNameMapping->id],
                                        [
                                            'class' => 'btn btn-xs btn-outline-danger',
                                            'escape' => false,
                                            'confirm' => __('Are you sure you want to delete # {0}?', $playerNameMapping->id),
                                            'title' => __('Delete')
                                        ]
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex flex-column flex-md-row align-items-center justify-content-between">
            <div class="text-muted small mb-2 mb-md-0">
                <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
            </div>
            <ul class="pagination pagination-sm mb-0">
                <?= $this->Paginator->first('<i class="fas fa-angle-double-left"></i>', ['escape' => false]) ?>
                <?= $this->Paginator->prev('<i class="fas fa-angle-left"></i>', ['escape' => false]) ?>
                <?= $this->Paginator->numbers() ?>
                <?= $this->Paginator->next('<i class="fas fa-angle-right"></i>', ['escape' => false]) ?>
                <?= $this->Paginator->last('<i class="fas fa-angle-double-right"></i>', ['escape' => false]) ?>
            </ul>
        </div>
    </div>
</div>