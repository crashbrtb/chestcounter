<?php
/**
 * The small event badge that sits with the goal pills on the score page.
 *
 * Two states, both an image an administrator can replace: a running event shows
 * that event's banner and links to its dashboard; nothing running shows the
 * "no event" artwork and links to the history, so the badge is never a dead end.
 *
 * The lookup happens here rather than in the controller so the badge can be
 * dropped into any page without that page having to know about events. It is
 * wrapped because the score page is the site's front door: a broken or not yet
 * migrated events table must not take it down.
 *
 * @var \App\View\AppView $this
 */

use App\Model\Entity\EventAsset;
use Cake\ORM\TableRegistry;

$runningEvent = null;
try {
    $runningEvent = TableRegistry::getTableLocator()->get('Events')->currentEvent();
} catch (\Throwable $e) {
    return;
}

if ($runningEvent !== null) {
    $imageUrl = $runningEvent->has_custom_banner
        ? $this->Url->build(['controller' => 'Events', 'action' => 'banner', $runningEvent->id])
        : $this->Url->build(['controller' => 'Events', 'action' => 'asset', EventAsset::SLUG_EVENT_LIVE]);
    $linkUrl = ['controller' => 'Events', 'action' => 'view', $runningEvent->id];
    $alt = __('Event running: {0}', $runningEvent->name);
    $title = __('Event #{0} - {1} - ends {2} UTC', $runningEvent->event_number, $runningEvent->name, $runningEvent->ends_at->format('d/m/Y H:i'));
    $stateClass = 'is-live';
} else {
    $imageUrl = $this->Url->build(['controller' => 'Events', 'action' => 'asset', EventAsset::SLUG_NO_EVENT]);
    $linkUrl = ['controller' => 'Events', 'action' => 'history'];
    $alt = __('No event running');
    $title = __('No event is running. Click to see past events.');
    $stateClass = 'is-idle';
}
?>
<a href="<?= $this->Url->build($linkUrl) ?>"
   class="event-badge <?= $stateClass ?>"
   title="<?= h($title) ?>">
    <img src="<?= h($imageUrl) ?>" alt="<?= h($alt) ?>">
    <?php if ($runningEvent !== null): ?>
        <span class="event-badge-dot" aria-hidden="true"></span>
    <?php endif; ?>
</a>
