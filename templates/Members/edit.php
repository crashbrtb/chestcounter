<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Member $member
 * @var array $users
 */

$this->assign('title', __('Edit Member'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Members'), 'url' => ['action' => 'index']],
    ['title' => __('View'), 'url' => ['action' => 'view', $member->id]],
    ['title' => __('Edit')],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-user-edit text-primary mr-2"></i><?= __('Edit Member') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Manage player profile, in-game identifier, troop levels, and ranking participation') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-eye mr-1"></i> ' . __('View'),
                ['action' => 'view', $member->id],
                ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i> ' . __('List Members'),
                ['action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <h3 class="card-title font-weight-bold mb-2 mb-md-0">
                <i class="fas fa-id-badge text-primary mr-2"></i><?= h($member->player) ?>
                <span class="text-muted small ml-1">#<?= $this->Number->format($member->id) ?></span>
            </h3>
            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                <?php if ($member->active): ?>
                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> <?= __('Active') ?></span>
                <?php else: ?>
                    <span class="badge badge-secondary"><i class="fas fa-user-slash mr-1"></i> <?= __('Inactive') ?></span>
                <?php endif; ?>

                <?php if ($member->administrative_account): ?>
                    <span class="badge badge-secondary"><i class="fas fa-user-shield mr-1"></i> <?= __('Administrative') ?></span>
                <?php else: ?>
                    <span class="badge badge-success"><i class="fas fa-coins mr-1"></i> <?= __('Receives Rewards') ?></span>
                <?php endif; ?>

                <?php if (!empty($member->game_player_id)): ?>
                    <span class="badge badge-light border"><i class="fas fa-gamepad text-primary mr-1"></i> ID: <?= h($member->game_player_id) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <?= $this->Form->create($member) ?>
        <div class="card-body">
            <div class="row">
                <!-- Coluna Esquerda: Detalhes do Jogador -->
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h5 class="text-primary font-weight-bold mb-3 pb-2 border-bottom">
                        <i class="fas fa-user-circle mr-2"></i><?= __('Player Details') ?>
                    </h5>

                    <!-- Nome do Jogador -->
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
                                'placeholder' => __('Player in-game name'),
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                    </div>

                    <!-- In-Game Player ID -->
                    <div class="form-group">
                        <label for="game-player-id" class="font-weight-bold"><?= __('Game Player ID') ?></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-fingerprint"></i></span>
                            </div>
                            <?= $this->Form->control('game_player_id', [
                                'label' => false,
                                'type' => 'text',
                                'class' => 'form-control',
                                'placeholder' => __('Filled in automatically by the first published tournament'),
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle mr-1"></i><?= __('Matches the member to tournament rankings even if their name changes.') ?>
                        </small>
                    </div>

                    <!-- Poder -->
                    <div class="form-group">
                        <label for="power" class="font-weight-bold"><?= __('Power') ?> <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-bolt text-warning"></i></span>
                            </div>
                            <?= $this->Form->control('power', [
                                'label' => false,
                                'type' => 'number',
                                'class' => 'form-control',
                                'required' => true,
                                'min' => 0,
                                'placeholder' => '0',
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                    </div>

                    <!-- Usuário do Sistema Vinculado -->
                    <?php if (isset($users)): ?>
                        <div class="form-group">
                            <label for="user-id" class="font-weight-bold"><?= __('Linked System User') ?></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                </div>
                                <?= $this->Form->control('user_id', [
                                    'label' => false,
                                    'options' => $users,
                                    'empty' => __('None (Not linked to any login account)'),
                                    'class' => 'form-control',
                                    'templates' => ['inputContainer' => '{{content}}'],
                                ]) ?>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle mr-1"></i><?= __('Associate this clan member with a web system login account.') ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Coluna Direita: Tropas e Status -->
                <div class="col-lg-6">
                    <h5 class="text-primary font-weight-bold mb-3 pb-2 border-bottom">
                        <i class="fas fa-shield-alt mr-2"></i><?= __('Troop Levels & Status') ?>
                    </h5>

                    <!-- Grid de Tropas -->
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="guards" class="font-weight-bold"><?= __('Guards') ?> <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-shield-alt text-primary"></i></span>
                                    </div>
                                    <?= $this->Form->control('guards', [
                                        'label' => false,
                                        'type' => 'number',
                                        'class' => 'form-control',
                                        'required' => true,
                                        'min' => 0,
                                        'templates' => ['inputContainer' => '{{content}}'],
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="specialists" class="font-weight-bold"><?= __('Specialists') ?> <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-crosshairs text-info"></i></span>
                                    </div>
                                    <?= $this->Form->control('specialists', [
                                        'label' => false,
                                        'type' => 'number',
                                        'class' => 'form-control',
                                        'required' => true,
                                        'min' => 0,
                                        'templates' => ['inputContainer' => '{{content}}'],
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="monsters" class="font-weight-bold"><?= __('Monsters') ?> <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-dragon text-danger"></i></span>
                                    </div>
                                    <?= $this->Form->control('monsters', [
                                        'label' => false,
                                        'type' => 'number',
                                        'class' => 'form-control',
                                        'required' => true,
                                        'min' => 0,
                                        'templates' => ['inputContainer' => '{{content}}'],
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="engineers" class="font-weight-bold"><?= __('Engineers') ?> <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-wrench text-secondary"></i></span>
                                    </div>
                                    <?= $this->Form->control('engineers', [
                                        'label' => false,
                                        'type' => 'number',
                                        'class' => 'form-control',
                                        'required' => true,
                                        'min' => 0,
                                        'templates' => ['inputContainer' => '{{content}}'],
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configurações de Status e Acesso -->
                    <div class="form-group p-3 rounded bg-light border mt-2">
                        <label class="font-weight-bold d-block mb-3"><?= __('Member Status & Clan Policy') ?></label>

                        <div class="mb-3">
                            <?= $this->Form->control('active', [
                                'type' => 'checkbox',
                                'custom' => true,
                                'label' => __('Active Member (currently in clan and active)'),
                            ]) ?>
                            <small class="form-text text-muted ml-4">
                                <?= __('Inactive members are hidden from common lists and ranking calculations.') ?>
                            </small>
                        </div>

                        <div class="pt-2 border-top">
                            <?= $this->Form->control('administrative_account', [
                                'type' => 'checkbox',
                                'custom' => true,
                                'label' => __('Administrative Account'),
                            ]) ?>
                            <small class="form-text text-muted ml-4">
                                <?= __('Takes part in tournament rankings but never receives tournament rewards or counts towards division points.') ?>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Metadados de Auditoria -->
                <div class="col-12 mt-3 pt-3 border-top d-flex flex-wrap justify-content-between text-muted small">
                    <div>
                        <i class="fas fa-calendar-plus mr-1"></i>
                        <strong><?= __('Created:') ?></strong>
                        <?= !empty($member->created_at) ? h($member->created_at->format('d/m/Y H:i:s')) : '-' ?>
                    </div>
                    <div>
                        <i class="fas fa-calendar-check mr-1"></i>
                        <strong><?= __('Last Modified:') ?></strong>
                        <?= !empty($member->modified_at) ? h($member->modified_at->format('d/m/Y H:i:s')) : '-' ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between">
            <div class="mb-2 mb-sm-0">
                <?= $this->Form->postLink(
                    '<i class="fas fa-trash-alt mr-1"></i> ' . __('Delete'),
                    ['action' => 'delete', $member->id],
                    [
                        'confirm' => __('Are you sure you want to delete # {0}?', $member->id),
                        'class' => 'btn btn-danger',
                        'escape' => false,
                    ]
                ) ?>
            </div>
            <div class="d-flex" style="gap: 8px;">
                <?= $this->Html->link(
                    '<i class="fas fa-times mr-1"></i> ' . __('Cancel'),
                    ['action' => 'view', $member->id],
                    ['class' => 'btn btn-default', 'escape' => false]
                ) ?>
                <?= $this->Form->button(
                    '<i class="fas fa-save mr-1"></i> ' . __('Save'),
                    ['class' => 'btn btn-primary', 'escapeTitle' => false]
                ) ?>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>