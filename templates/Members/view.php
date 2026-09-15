<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Member $member
 */

$this->assign('title', __('Member: {0}', $member->player));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Members'), 'url' => ['action' => 'index']],
    ['title' => h($member->player)],
]);
?>

<div class="content-page-wrap">
    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-id-card text-primary mr-2"></i><?= h($member->player) ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('Member profile, troop composition, and clan participation details') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-edit mr-1"></i> ' . __('Edit'),
                ['action' => 'edit', $member->id],
                ['class' => 'btn btn-primary btn-sm', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i> ' . __('List Members'),
                ['action' => 'index'],
                ['class' => 'btn btn-default btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <!-- Card de Resumo e Estatísticas Rápidas -->
    <div class="row mb-3">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card h-100 shadow-sm border-0 bg-primary text-white p-3 d-flex flex-column justify-content-between">
                <div>
                    <span class="text-white-50 text-uppercase small font-weight-bold"><?= __('Total Power') ?></span>
                    <h2 class="font-weight-bold mt-1 mb-0">
                        <i class="fas fa-bolt mr-2 text-warning"></i><?= $this->Number->format($member->power) ?>
                    </h2>
                </div>
                <div class="mt-3 pt-2 border-top border-white-50 small">
                    <?= __('Clan Member # {0}', $this->Number->format($member->id)) ?>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card h-100 shadow-sm border">
                <div class="card-header bg-light py-2">
                    <h6 class="font-weight-bold mb-0 text-muted">
                        <i class="fas fa-shield-alt mr-1 text-primary"></i> <?= __('Troop Composition') ?>
                    </h6>
                </div>
                <div class="card-body py-3">
                    <div class="row text-center">
                        <div class="col-3 border-right">
                            <span class="text-muted small d-block font-weight-bold"><?= __('Guards') ?></span>
                            <span class="h4 font-weight-bold text-primary mb-0 d-block mt-1">
                                <i class="fas fa-shield-alt mr-1"></i><?= $this->Number->format($member->guards) ?>
                            </span>
                        </div>
                        <div class="col-3 border-right">
                            <span class="text-muted small d-block font-weight-bold"><?= __('Specialists') ?></span>
                            <span class="h4 font-weight-bold text-info mb-0 d-block mt-1">
                                <i class="fas fa-crosshairs mr-1"></i><?= $this->Number->format($member->specialists) ?>
                            </span>
                        </div>
                        <div class="col-3 border-right">
                            <span class="text-muted small d-block font-weight-bold"><?= __('Monsters') ?></span>
                            <span class="h4 font-weight-bold text-danger mb-0 d-block mt-1">
                                <i class="fas fa-dragon mr-1"></i><?= $this->Number->format($member->monsters) ?>
                            </span>
                        </div>
                        <div class="col-3">
                            <span class="text-muted small d-block font-weight-bold"><?= __('Engineers') ?></span>
                            <span class="h4 font-weight-bold text-secondary mb-0 d-block mt-1">
                                <i class="fas fa-wrench mr-1"></i><?= $this->Number->format($member->engineers) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <h3 class="card-title font-weight-bold mb-2 mb-md-0">
                <i class="fas fa-info-circle text-primary mr-2"></i><?= __('Account & Clan Data') ?>
            </h3>
            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                <?php if ($member->active): ?>
                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> <?= __('Active Member') ?></span>
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

        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <tbody>
                    <tr>
                        <th style="width: 250px;" class="bg-light"><?= __('Player Name') ?></th>
                        <td class="font-weight-bold"><?= h($member->player) ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Internal ID') ?></th>
                        <td>#<?= $this->Number->format($member->id) ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Game Player ID') ?></th>
                        <td>
                            <?php if (!empty($member->game_player_id)): ?>
                                <code><?= h($member->game_player_id) ?></code>
                            <?php else: ?>
                                <span class="text-muted"><i class="fas fa-minus mr-1"></i> <?= __('Not assigned yet') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Status') ?></th>
                        <td>
                            <?php if ($member->active): ?>
                                <span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> <?= __('Active') ?></span>
                            <?php else: ?>
                                <span class="text-muted"><i class="fas fa-times-circle mr-1"></i> <?= __('Inactive') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Tournament Rewards') ?></th>
                        <td>
                            <?php if ($member->administrative_account): ?>
                                <span class="text-muted font-weight-bold"><i class="fas fa-user-shield mr-1"></i> <?= __('Administrative Account (Excluded from tournament prize distribution)') ?></span>
                            <?php else: ?>
                                <span class="text-success font-weight-bold"><i class="fas fa-coins mr-1"></i> <?= __('Eligible for tournament rewards') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Linked System User') ?></th>
                        <td>
                            <?php if (!empty($member->user)): ?>
                                <?= $this->Html->link(
                                    '<i class="fas fa-user mr-1 text-primary"></i> ' . h($member->user->name) . ' (' . h($member->user->email) . ')',
                                    ['controller' => 'Users', 'action' => 'view', $member->user->id],
                                    ['escape' => false]
                                ) ?>
                            <?php else: ?>
                                <span class="text-muted"><?= __('Not linked to any login account') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Created At') ?></th>
                        <td><?= !empty($member->created_at) ? h($member->created_at->format('d/m/Y H:i:s')) : '-' ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light"><?= __('Modified At') ?></th>
                        <td><?= !empty($member->modified_at) ? h($member->modified_at->format('d/m/Y H:i:s')) : '-' ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between">
            <div class="mb-2 mb-sm-0">
                <?= $this->Form->postLink(
                    '<i class="fas fa-trash-alt mr-1"></i> ' . __('Delete Member'),
                    ['action' => 'delete', $member->id],
                    [
                        'confirm' => __('Are you sure you want to delete {0}?', $member->player),
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
                    '<i class="fas fa-edit mr-1"></i> ' . __('Edit Member'),
                    ['action' => 'edit', $member->id],
                    ['class' => 'btn btn-primary', 'escape' => false]
                ) ?>
            </div>
        </div>
    </div>
</div>
