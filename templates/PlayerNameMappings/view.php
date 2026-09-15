<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\PlayerNameMapping $playerNameMapping
 */

$this->assign('title', __('View Player Name Mapping'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Player Name Mappings'), 'url' => ['action' => 'index']],
    ['title' => __('View')],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-exchange-alt text-primary mr-2"></i><?= __('View Mapping') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('OCR recognition rule mapping detail') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-edit mr-1"></i> ' . __('Edit'),
                ['action' => 'edit', $playerNameMapping->id],
                ['class' => 'btn btn-primary btn-sm', 'escape' => false]
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

        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <tbody>
                    <tr>
                        <th style="width: 250px;" class="bg-light"><?= __('Scanned OCR Text') ?></th>
                        <td><code class="text-danger font-weight-bold"><?= h($playerNameMapping->ocr_text) ?></code></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Correct Clan Name') ?></th>
                        <td><span class="badge badge-success font-weight-bold px-2 py-1"><i class="fas fa-user mr-1"></i><?= h($playerNameMapping->correct_name) ?></span></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Rule ID') ?></th>
                        <td>#<?= $this->Number->format($playerNameMapping->id) ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Created') ?></th>
                        <td><?= !empty($playerNameMapping->created) ? h($playerNameMapping->created->format('d/m/Y H:i:s')) : '-' ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Modified') ?></th>
                        <td><?= !empty($playerNameMapping->modified) ? h($playerNameMapping->modified->format('d/m/Y H:i:s')) : '-' ?></td>
                    </tr>
                </tbody>
            </table>
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
                    '<i class="fas fa-arrow-left mr-1"></i> ' . __('Back to List'),
                    ['action' => 'index'],
                    ['class' => 'btn btn-default', 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-edit mr-1"></i> ' . __('Edit'),
                    ['action' => 'edit', $playerNameMapping->id],
                    ['class' => 'btn btn-primary', 'escape' => false]
                ) ?>
            </div>
        </div>
    </div>
</div>