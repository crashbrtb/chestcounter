<?php
/**
 * Replace the two default banners the scoreboard shows.
 *
 * These are the fallbacks: the "event running" one is used by any event that has
 * no artwork of its own, and the "no event" one is what players see the rest of
 * the time.
 *
 * @var \App\View\AppView $this
 * @var array<string, \App\Model\Entity\EventAsset> $current
 */

use App\Model\Entity\EventAsset;

$this->assign('title', __('Event Banners'));

$slots = [
    EventAsset::SLUG_EVENT_LIVE => [
        'title' => __('Event running'),
        'icon' => 'fa-trophy',
        'hint' => __('Shown on the scoreboard while an event is running, unless that event has its own banner.'),
    ],
    EventAsset::SLUG_NO_EVENT => [
        'title' => __('No event'),
        'icon' => 'fa-moon',
        'hint' => __('Shown on the scoreboard when nothing is running.'),
    ],
];
?>
<div class="content-page-wrap">

    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title"><i class="fas fa-image text-primary"></i> <?= __('Event Banners') ?></h1>
            <p class="cycle-subtitle"><?= __('The default artwork shown beside the goals on the scoreboard') ?></p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i>' . __('Manage Events'),
                ['action' => 'manage'],
                ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <?= $this->Form->create(null, ['type' => 'file']) ?>

    <div class="event-info-grid">
        <?php foreach ($slots as $slug => $slot): ?>
            <?php $asset = $current[$slug] ?? null; ?>
            <div class="event-form-card">
                <h2><i class="fas <?= h($slot['icon']) ?> text-primary"></i> <?= h($slot['title']) ?></h2>
                <p class="section-hint"><?= h($slot['hint']) ?></p>

                <?php if ($asset !== null): ?>
                    <img class="banner-preview" id="preview-<?= h($slug) ?>"
                         src="<?= $this->Url->build(['action' => 'asset', $slug]) ?>?v=<?= h($asset->modified ? $asset->modified->getTimestamp() : 0) ?>"
                         alt="<?= h($slot['title']) ?>">
                    <p class="text-muted" style="font-size: 0.8rem;">
                        <?= __('Last changed: {0}', $asset->modified ? $asset->modified->format('d/m/Y H:i') . ' UTC' : '-') ?>
                    </p>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <?= __('This banner has not been set up. Upload one below.') ?>
                    </div>
                <?php endif; ?>

                <div class="form-group mb-0">
                    <label for="file-<?= h($slug) ?>"><?= __('Upload a replacement') ?></label>
                    <input type="file" name="<?= h($slug) ?>" id="file-<?= h($slug) ?>" class="form-control-file"
                           accept="image/png,image/jpeg,image/gif,image/webp"
                           onchange="previewSlot(this, 'preview-<?= h($slug) ?>')">
                    <small class="form-text text-muted">
                        <?= __('PNG, JPEG, GIF or WebP, up to 3 MB. A wide strip works best, around 640 x 160 pixels.') ?>
                    </small>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="event-form-actions">
        <?= $this->Form->button(
            '<i class="fas fa-save mr-1"></i>' . __('Save banners'),
            ['class' => 'btn btn-primary', 'escapeTitle' => false]
        ) ?>
    </div>

    <?= $this->Form->end() ?>
</div>

<?php $this->start('script'); ?>
<script>
    function previewSlot(input, targetId) {
        if (!input.files || !input.files[0]) {
            return;
        }
        var target = document.getElementById(targetId);
        if (!target) {
            return;
        }
        var reader = new FileReader();
        reader.onload = function (e) {
            target.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
</script>
<?php $this->end(); ?>
