<?php
/**
 * Shown when nothing is running: says so plainly, then points at what is next
 * and what just happened, so the page is still worth arriving at.
 *
 * @var \App\View\AppView $this
 * @var array<\App\Model\Entity\Event> $upcoming
 * @var array<\App\Model\Entity\Event> $recent
 */

use App\Model\Entity\Event;
use App\Model\Entity\EventAsset;

$this->assign('title', __('Events'));
?>
<div class="content-page-wrap">

    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title"><i class="fas fa-trophy text-warning"></i> <?= __('Events') ?></h1>
            <p class="cycle-subtitle"><?= __('Tournaments, prizes and standings') ?></p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-history mr-1"></i>' . __('Event History'),
                ['action' => 'history'],
                ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="event-hero is-finished">
        <div class="event-hero-inner">
            <div class="event-hero-main">
                <span class="event-hero-eyebrow">
                    <i class="fas fa-moon"></i> <?= __('Nothing running') ?>
                </span>
                <h1><?= __('No event is running right now') ?></h1>
                <p class="event-hero-description">
                    <?= __('When an administrator opens an event, it appears here and a banner shows up on the scoreboard.') ?>
                </p>
            </div>
            <div class="event-hero-banner">
                <img src="<?= $this->Url->build(['action' => 'asset', EventAsset::SLUG_NO_EVENT]) ?>"
                     alt="<?= __('No event running') ?>">
            </div>
        </div>
    </div>

    <?php if (!empty($upcoming)): ?>
        <h2 class="score-title" style="font-size: 1.1rem; margin-bottom: 12px;">
            <i class="fas fa-hourglass-start text-info"></i> <?= __('Coming up') ?>
        </h2>
        <div class="event-history-list mb-4">
            <?php foreach ($upcoming as $event): ?>
                <?= $this->element('event_row', ['event' => $event]) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($recent)): ?>
        <h2 class="score-title" style="font-size: 1.1rem; margin-bottom: 12px;">
            <i class="fas fa-flag-checkered text-muted"></i> <?= __('Recently finished') ?>
        </h2>
        <div class="event-history-list">
            <?php foreach ($recent as $event): ?>
                <?= $this->element('event_row', ['event' => $event]) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($upcoming) && empty($recent)): ?>
        <div class="event-standings-card">
            <div class="event-empty">
                <i class="fas fa-calendar-day"></i>
                <p><?= __('No event has been created yet.') ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>
