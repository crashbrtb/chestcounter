<?php
/**
 * One event as a row in a list. Shared by the history page and the "no event"
 * page so an event looks the same wherever it is listed.
 *
 * The winner is read from the stored standings, which only exist once an event
 * has been closed; a finished but unrecorded event simply shows no winner rather
 * than recomputing from chest data that may already have been purged.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 */

use App\Model\Entity\Event;
use Cake\ORM\TableRegistry;

$state = $event->state;

$stateLabels = [
    Event::STATE_RUNNING => __('Running'),
    Event::STATE_SCHEDULED => __('Scheduled'),
    Event::STATE_FINISHED => __('Finished'),
    Event::STATE_CANCELLED => __('Cancelled'),
    Event::STATE_AWAITING => __('Awaiting result'),
];
$stateIcons = [
    Event::STATE_RUNNING => 'fa-play-circle',
    Event::STATE_SCHEDULED => 'fa-hourglass-start',
    Event::STATE_FINISHED => 'fa-flag-checkered',
    Event::STATE_CANCELLED => 'fa-ban',
    Event::STATE_AWAITING => 'fa-hourglass-half',
];

// A running event has a leader, not a winner: only a closed one is announced
// with a name, however early somebody recorded its standings.
$winner = null;
if ($event->finalized_at !== null && $state !== Event::STATE_RUNNING) {
    $winner = TableRegistry::getTableLocator()->get('EventStandings')->find()
        ->where(['event_id' => $event->id, 'position' => 1])
        ->first();
}
?>
<div class="event-history-item state-<?= h($state) ?>">
    <div class="event-history-main">
        <div class="event-history-title">
            <span class="event-number">#<?= h($event->event_number) ?></span>
            <?= $this->Html->link($event->name, ['controller' => 'Events', 'action' => 'view', $event->id]) ?>
        </div>
        <div class="event-history-meta">
            <span>
                <i class="fas fa-bullseye"></i> <?= h($event->criteriaLabel()) ?>
            </span>
            <span>
                <i class="fas fa-calendar"></i>
                <?php if ($event->is_imported): ?>
                    <?= h($event->starts_at->format('d/m/Y')) ?>
                <?php else: ?>
                    <?= h($event->starts_at->format('d/m/Y H:i')) ?>
                    &rarr;
                    <?= h($event->ends_at->format('d/m/Y H:i')) ?> UTC
                <?php endif; ?>
            </span>
            <span>
                <i class="fas fa-gift"></i>
                <?= h(mb_strimwidth((string)$event->prize, 0, 60, '...')) ?>
            </span>
        </div>
    </div>

    <div class="event-history-aside">
        <?php if ($winner !== null): ?>
            <div class="event-winner">
                <span class="event-winner-label"><?= __('Winner') ?></span>
                <span class="event-winner-name">
                    <i class="fas fa-trophy"></i> <?= h($winner->player) ?>
                </span>
            </div>
        <?php endif; ?>

        <span class="event-state state-<?= h($state) ?>">
            <i class="fas <?= h($stateIcons[$state]) ?>"></i> <?= h($stateLabels[$state]) ?>
        </span>

        <?= $this->Html->link(
            '<i class="fas fa-arrow-right"></i>',
            ['controller' => 'Events', 'action' => 'view', $event->id],
            [
                'class' => 'btn btn-outline-primary btn-sm',
                'escape' => false,
                'title' => __('Open event'),
            ]
        ) ?>
    </div>
</div>
