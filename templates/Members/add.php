<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Member $member
 * @var array $users
 */

$this->assign('title', __('Add Member'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Members'), 'url' => ['action' => 'index']],
    ['title' => __('Add')],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-user-plus text-primary mr-2"></i><?= __('Add Member') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Register a new clan member, configure troop levels, and ranking participation') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i> ' . __('List Members'),
                ['action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-id-card text-primary mr-2"></i><?= __('New Member Profile') ?>
            </h3>
        </div>

        <?= $this->Form->create($member, ['valueSources' => ['query', 'context']]) ?>
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
                            <i class="fas fa-info-circle mr-1"></i><?= __('Optional. Matches the member to tournament rankings even if their name changes.') ?>
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
                                'default' => 0,
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
                                        'default' => 1,
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
                                        'default' => 1,
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
                                        'default' => 1,
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
                                        'default' => 1,
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
                                'default' => 1,
                                'checked' => true,
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
            </div>
        </div>

        <div class="card-footer d-flex justify-content-end" style="gap: 8px;">
            <?= $this->Html->link(
                '<i class="fas fa-times mr-1"></i> ' . __('Cancel'),
                ['action' => 'index'],
                ['class' => 'btn btn-default', 'escape' => false]
            ) ?>
            <?= $this->Form->button(
                '<i class="fas fa-save mr-1"></i> ' . __('Save Member'),
                ['class' => 'btn btn-primary', 'escapeTitle' => false]
            ) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>