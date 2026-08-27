<?php
/**
 * @var \App\View\AppView $this
 * @var string[] $playerList
 * @var string $title
 */
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div>
            <h1 class="score-title"><?= h($title) ?></h1>
            <p class="cycle-subtitle"><?= __('Select the correct player name to keep and the incorrect name to replace') ?></p>
        </div>
        <div class="actions">
            <?= $this->Html->link('<i class="fas fa-arrow-left mr-1"></i> ' . __('Back to Score'), ['action' => 'score'], ['class' => 'btn btn-default btn-sm', 'escape' => false]) ?>
        </div>
    </div>

    <div class="card ranking-card mb-4">
        <div class="ranking-header">
            <h3 class="card-title"><?= __('Merge Player Names') ?></h3>
        </div>
        <div class="card-body">
            <p class="text-muted">
                <?= __('All collected chest records associated with the incorrect name will be reassigned to the correct name. This action cannot be undone.') ?>
            </p>

            <?= $this->Form->create(null, ['url' => ['action' => 'mergePlayers']]) ?>
            <div class="form-group">
                <?= $this->Form->control('correct_player_name', [
                    'options' => $playerList,
                    'empty' => __('Select Correct Name'),
                    'label' => __('Correct Player Name (Keep this one)'),
                    'class' => 'form-control',
                    'required' => true,
                ]) ?>
            </div>
            <div class="form-group">
                <?= $this->Form->control('incorrect_player_name', [
                    'options' => $playerList,
                    'empty' => __('Select Incorrect Name'),
                    'label' => __('Incorrect Player Name (Replace this one)'),
                    'class' => 'form-control',
                    'required' => true,
                ]) ?>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <?= $this->Form->button('<i class="fas fa-compress-alt mr-1"></i> ' . __('Merge Players'), ['class' => 'btn btn-primary', 'escapeTitle' => false]) ?>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </div>

    <div class="card ranking-card">
        <div class="ranking-header">
            <h3 class="card-title"><?= __('Current Unique Player List') ?> (<?= count($playerList) ?>)</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($playerList)): ?>
                <div class="d-flex flex-wrap" style="gap: 8px;">
                    <?php foreach ($playerList as $player): ?>
                        <span class="badge badge-light p-2" style="font-size: 0.88rem;"><?= h($player) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0"><?= __('No player names found.') ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
