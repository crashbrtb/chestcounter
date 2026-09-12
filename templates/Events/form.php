<?php
/**
 * Create or edit an event.
 *
 * Times are entered and shown as UTC throughout, matching how the rest of the
 * application stores and compares them; the browser's own zone is never used,
 * and the page says so next to every date field.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 * @var array<\App\Model\Entity\StandardChest> $scoredChests
 * @var list<int> $selectedChestIds
 * @var array<string, string> $criteriaOptions
 * @var array<string, string> $criteriaHints
 * @var int $nextNumber
 */

use App\Model\Entity\Event;
use App\Model\Entity\EventAsset;
use Cake\I18n\DateTime;

$isNew = $event->isNew();
$this->assign('title', $isNew ? __('New Event') : __('Edit Event'));

// datetime-local wants "Y-m-dTH:i". A freshly opened form starts an hour out,
// which is a sane default for "soon" and safely clear of the not-in-the-past rule.
$formatForInput = function ($value): string {
    return $value instanceof \DateTimeInterface ? $value->format('Y-m-d\TH:i') : '';
};

$startsValue = $formatForInput($event->starts_at) ?: DateTime::now()->addHours(1)->format('Y-m-d\TH:i');
$endsValue = $formatForInput($event->ends_at) ?: DateTime::now()->addDays(7)->format('Y-m-d\TH:i');
$nowForMin = DateTime::now()->format('Y-m-d\TH:i');

$criteriaIcons = [
    Event::CRITERIA_CHEST_COUNT => 'fa-boxes',
    Event::CRITERIA_CHEST_SCORE => 'fa-star',
    Event::CRITERIA_EPIC_MONSTER => 'fa-dragon',
    Event::CRITERIA_CUSTOM_CHESTS => 'fa-sliders-h',
];

$currentCriteria = $event->criteria ?: Event::CRITERIA_CHEST_SCORE;

/** Renders the validation message for a field, if the save left one. */
$fieldError = function (string $field) use ($event) {
    $errors = $event->getError($field);
    if (!$errors) {
        return '';
    }

    return '<span class="field-error"><i class="fas fa-exclamation-circle mr-1"></i>'
        . h(implode(' ', (array)$errors)) . '</span>';
};
?>
<div class="content-page-wrap">

    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-<?= $isNew ? 'plus' : 'pen' ?> text-primary"></i>
                <?= $isNew ? __('New Event') : __('Edit Event #{0}', $event->event_number) ?>
            </h1>
            <p class="cycle-subtitle">
                <?= $isNew
                    ? __('It will be created as event #{0}', $nextNumber)
                    : __('Created {0}', $event->created ? $event->created->format('d/m/Y H:i') . ' UTC' : '-') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i>' . __('Manage Events'),
                ['action' => 'manage'],
                ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <?= $this->Form->create($event, ['type' => 'file', 'id' => 'eventForm']) ?>

    <!-- Identity -->
    <div class="event-form-card">
        <h2><i class="fas fa-tag text-primary"></i> <?= __('Identification') ?></h2>
        <p class="section-hint"><?= __('The name players will see, and an optional explanation of the rules.') ?></p>

        <div class="event-form-grid">
            <div class="form-group">
                <label for="event-number-display"><?= __('Event number') ?></label>
                <input type="text" id="event-number-display" class="form-control"
                       value="#<?= h($isNew ? $nextNumber : $event->event_number) ?>" disabled>
                <small class="form-text text-muted">
                    <?= __('Assigned automatically and never reused.') ?>
                </small>
            </div>

            <div class="form-group">
                <?= $this->Form->control('name', [
                    'label' => __('Event name'),
                    'class' => 'form-control',
                    'required' => true,
                    'maxlength' => 120,
                    'placeholder' => __('e.g. September Chest Marathon'),
                    'templates' => ['inputContainer' => '{{content}}'],
                    'error' => false,
                ]) ?>
                <?= $fieldError('name') ?>
            </div>

            <div class="form-group full-width">
                <?= $this->Form->control('description', [
                    'label' => __('Description (optional)'),
                    'type' => 'textarea',
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => __('Anything the players should know about this event.'),
                    'templates' => ['inputContainer' => '{{content}}'],
                    'error' => false,
                ]) ?>
            </div>
        </div>
    </div>

    <!-- Window -->
    <div class="event-form-card">
        <h2>
            <i class="fas fa-clock text-primary"></i> <?= __('When it runs') ?>
            <span class="utc-note"><i class="fas fa-globe"></i> UTC</span>
        </h2>
        <p class="section-hint">
            <?= __('Both moments are UTC, the same clock the scoreboard uses. Only chests collected inside this window count, and the start must be in the future.') ?>
        </p>

        <div class="event-form-grid">
            <div class="form-group">
                <label for="starts-at"><?= __('Starts at (UTC)') ?></label>
                <input type="datetime-local" class="form-control" id="starts-at" name="starts_at"
                       value="<?= h($startsValue) ?>" min="<?= h($nowForMin) ?>" step="60" required>
                <?= $fieldError('starts_at') ?>
            </div>

            <div class="form-group">
                <label for="ends-at"><?= __('Ends at (UTC)') ?></label>
                <input type="datetime-local" class="form-control" id="ends-at" name="ends_at"
                       value="<?= h($endsValue) ?>" min="<?= h($nowForMin) ?>" step="60" required>
                <?= $fieldError('ends_at') ?>
            </div>

            <div class="form-group full-width">
                <div class="alert alert-info mb-0" style="font-size: 0.85rem;">
                    <i class="fas fa-info-circle mr-1"></i>
                    <?= __('Right now it is {0} UTC.', DateTime::now()->format('d/m/Y H:i')) ?>
                    <span id="durationHint" class="ml-2 font-weight-bold"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Criteria -->
    <div class="event-form-card">
        <h2><i class="fas fa-bullseye text-primary"></i> <?= __('What counts') ?></h2>
        <p class="section-hint"><?= __('How players are ranked in this event.') ?></p>

        <div class="criteria-options">
            <?php foreach ($criteriaOptions as $value => $label): ?>
                <label class="criteria-option">
                    <input type="radio" name="criteria" value="<?= h($value) ?>"
                           <?= $currentCriteria === $value ? 'checked' : '' ?>
                           onchange="onCriteriaChange()">
                    <span class="criteria-option-body">
                        <strong>
                            <i class="fas <?= h($criteriaIcons[$value] ?? 'fa-circle') ?>"></i>
                            <?= h($label) ?>
                        </strong>
                        <span><?= h($criteriaHints[$value] ?? '') ?></span>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
        <?= $fieldError('criteria') ?>

        <!-- Custom chest picker -->
        <div id="customChestSection" class="mt-4" style="display: none;">
            <hr>
            <h2 class="mt-3"><i class="fas fa-box-open text-primary"></i> <?= __('Chests in this event') ?></h2>
            <p class="section-hint">
                <?= __('Only chests that carry a score can be picked, because a chest worth nothing cannot decide a ranking. Choose as many as you like.') ?>
            </p>

            <div class="form-group">
                <label><?= __('Rank players by') ?></label>
                <div class="d-flex flex-wrap" style="gap: 18px;">
                    <label class="form-check-label" style="cursor: pointer;">
                        <input type="radio" name="custom_metric" value="<?= Event::METRIC_SCORE ?>"
                               <?= ($event->custom_metric ?? Event::METRIC_SCORE) !== Event::METRIC_COUNT ? 'checked' : '' ?>>
                        <?= __('Total score of the chosen chests') ?>
                    </label>
                    <label class="form-check-label" style="cursor: pointer;">
                        <input type="radio" name="custom_metric" value="<?= Event::METRIC_COUNT ?>"
                               <?= ($event->custom_metric ?? '') === Event::METRIC_COUNT ? 'checked' : '' ?>>
                        <?= __('Number of the chosen chests collected') ?>
                    </label>
                </div>
            </div>

            <div class="chest-picker-toolbar">
                <div class="event-search" style="flex: 1 1 240px;">
                    <i class="fas fa-search"></i>
                    <input type="text" id="chestSearch" placeholder="<?= __('Filter chests...') ?>"
                           onkeyup="filterChests(this.value)">
                </div>
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <span class="chest-picker-count" id="chestCount">0</span>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setAllChests(true)">
                        <?= __('Select all shown') ?>
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setAllChests(false)">
                        <?= __('Clear') ?>
                    </button>
                </div>
            </div>

            <?php if (empty($scoredChests)): ?>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <?= __('No chest has a score yet. Set chest scores before creating a custom chest event.') ?>
                </div>
            <?php else: ?>
                <div class="chest-picker" id="chestPicker">
                    <?php foreach ($scoredChests as $chest): ?>
                        <label class="chest-option" data-name="<?= h(mb_strtolower($chest->display_name . ' ' . $chest->source)) ?>">
                            <input type="checkbox" name="chest_ids[]" value="<?= (int)$chest->id ?>"
                                   <?= in_array((int)$chest->id, $selectedChestIds, true) ? 'checked' : '' ?>
                                   onchange="updateChestCount()">
                            <?php if (!empty($chest->monster)): ?>
                                <i class="fas fa-dragon chest-option-monster" title="<?= __('Epic monster chest') ?>"></i>
                            <?php endif; ?>
                            <span class="chest-option-name" title="<?= h($chest->source) ?>">
                                <?= h($chest->display_name) ?>
                            </span>
                            <span class="chest-option-score"><?= $this->Number->format($chest->score) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?= $fieldError('event_chests') ?>
        </div>
    </div>

    <!-- Prize and contact -->
    <div class="event-form-card">
        <h2><i class="fas fa-gift text-primary"></i> <?= __('Prize and contact') ?></h2>
        <p class="section-hint"><?= __('What the winner gets, and who they should look for to claim it.') ?></p>

        <div class="event-form-grid">
            <div class="form-group full-width">
                <?= $this->Form->control('prize', [
                    'label' => __('Prize'),
                    'type' => 'textarea',
                    'class' => 'form-control',
                    'rows' => 3,
                    'required' => true,
                    'placeholder' => __('e.g. 500 gold for first place, 250 for second.'),
                    'templates' => ['inputContainer' => '{{content}}'],
                    'error' => false,
                ]) ?>
                <?= $fieldError('prize') ?>
            </div>

            <div class="form-group">
                <?= $this->Form->control('contact_player', [
                    'label' => __('Player to look for'),
                    'class' => 'form-control',
                    'required' => true,
                    'maxlength' => 120,
                    'placeholder' => __('e.g. Ventura'),
                    'templates' => ['inputContainer' => '{{content}}'],
                    'error' => false,
                ]) ?>
                <?= $fieldError('contact_player') ?>
            </div>
        </div>
    </div>

    <!-- Banner -->
    <div class="event-form-card">
        <h2><i class="fas fa-image text-primary"></i> <?= __('Event banner') ?></h2>
        <p class="section-hint">
            <?= __('Shown on the scoreboard next to the goals while this event is running. Leave it empty to use the default banner.') ?>
        </p>

        <img class="banner-preview" id="bannerPreview"
             src="<?= $event->has_custom_banner
                 ? $this->Url->build(['action' => 'banner', $event->id])
                 : $this->Url->build(['action' => 'asset', EventAsset::SLUG_EVENT_LIVE]) ?>"
             alt="<?= __('Banner preview') ?>">

        <div class="form-group">
            <label for="banner"><?= __('Replace the banner') ?></label>
            <input type="file" name="banner" id="banner" class="form-control-file"
                   accept="image/png,image/jpeg,image/gif,image/webp" onchange="previewBanner(this)">
            <small class="form-text text-muted">
                <?= __('PNG, JPEG, GIF or WebP, up to 3 MB. A wide strip works best, around 640 x 160 pixels.') ?>
            </small>
        </div>

        <?php if (!$isNew && $event->has_custom_banner): ?>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="remove-banner" name="remove_banner" value="1">
                <label class="form-check-label" for="remove-banner">
                    <?= __('Drop this banner and go back to the default') ?>
                </label>
            </div>
        <?php endif; ?>
    </div>

    <div class="event-form-actions">
        <?= $this->Html->link(__('Cancel'), ['action' => 'manage'], ['class' => 'btn btn-default spacer']) ?>
        <?= $this->Form->button(
            '<i class="fas fa-save mr-1"></i>' . ($isNew ? __('Create event') : __('Save changes')),
            ['class' => 'btn btn-primary', 'escapeTitle' => false]
        ) ?>
    </div>

    <?= $this->Form->end() ?>
</div>

<?php $this->start('script'); ?>
<script>
    var CUSTOM_CRITERIA = '<?= Event::CRITERIA_CUSTOM_CHESTS ?>';

    // The chest list only means anything for the custom criteria, so it is only
    // on screen when that is what was chosen.
    function onCriteriaChange() {
        var selected = document.querySelector('input[name="criteria"]:checked');
        var section = document.getElementById('customChestSection');
        section.style.display = selected && selected.value === CUSTOM_CRITERIA ? 'block' : 'none';
    }

    function updateChestCount() {
        var checked = document.querySelectorAll('#chestPicker input[type="checkbox"]:checked').length;
        var badge = document.getElementById('chestCount');
        if (badge) {
            badge.textContent = checked === 1
                ? '<?= __('1 chest selected') ?>'
                : '<?= __('{0} chests selected') ?>'.replace('{0}', checked);
        }
    }

    function filterChests(term) {
        var needle = (term || '').toLowerCase().trim();
        document.querySelectorAll('#chestPicker .chest-option').forEach(function (option) {
            var name = option.getAttribute('data-name') || '';
            option.style.display = needle === '' || name.indexOf(needle) !== -1 ? '' : 'none';
        });
    }

    // Acts on what is visible: with a filter applied, "select all shown" should
    // not quietly tick the hundreds of rows the filter hid.
    function setAllChests(checked) {
        document.querySelectorAll('#chestPicker .chest-option').forEach(function (option) {
            if (option.style.display === 'none') {
                return;
            }
            var box = option.querySelector('input[type="checkbox"]');
            if (box) {
                box.checked = checked;
            }
        });
        updateChestCount();
    }

    function previewBanner(input) {
        if (!input.files || !input.files[0]) {
            return;
        }
        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('bannerPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }

    // Says how long the event will last, so a wrong date is obvious before saving
    // rather than after the event has been announced.
    function updateDuration() {
        var start = document.getElementById('starts-at').value;
        var end = document.getElementById('ends-at').value;
        var hint = document.getElementById('durationHint');
        if (!start || !end) {
            hint.textContent = '';
            return;
        }

        var ms = new Date(end + 'Z') - new Date(start + 'Z');
        if (ms <= 0) {
            hint.className = 'ml-2 font-weight-bold text-danger';
            hint.textContent = '<?= __('The end must come after the start.') ?>';
            return;
        }

        var hours = Math.floor(ms / 3600000);
        var days = Math.floor(hours / 24);
        hint.className = 'ml-2 font-weight-bold';
        hint.textContent = days >= 1
            ? '<?= __('Duration: {0} day(s) and {1} hour(s).') ?>'.replace('{0}', days).replace('{1}', hours % 24)
            : '<?= __('Duration: {0} hour(s).') ?>'.replace('{0}', hours);
    }

    document.addEventListener('DOMContentLoaded', function () {
        onCriteriaChange();
        updateChestCount();
        updateDuration();
        document.getElementById('starts-at').addEventListener('change', updateDuration);
        document.getElementById('ends-at').addEventListener('change', updateDuration);
    });
</script>
<?php $this->end(); ?>
