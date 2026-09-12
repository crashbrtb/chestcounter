<?php
/**
 * Choose the colour scheme the whole site wears.
 *
 * Each card draws a small mock of a page in its own theme, painted from the
 * swatches rather than from the stylesheet: that way an administrator can see
 * all four at once instead of having to apply one to find out what it is.
 *
 * @var \App\View\AppView $this
 * @var array<string, array{label: string, blurb: string, dark: bool, swatches: array{bg: string, card: string, accent: string, text: string}}> $themes
 * @var string $chosen
 */

$this->assign('title', __('Theme'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('Config'), 'url' => ['action' => 'index']],
    ['title' => __('Theme')],
]);

$this->Html->css('branding', ['block' => 'css']);
?>
<div class="content-page-wrap theme-page">

    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-palette text-primary"></i> <?= __('Theme') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('The colours every page of the site is drawn in') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-shield-alt mr-1"></i>' . __('Branding'),
                ['action' => 'branding'],
                ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <?= $this->Form->create() ?>

    <div class="brand-section">
        <div class="brand-section-head">
            <h2><i class="fas fa-swatchbook text-primary"></i> <?= __('Colour scheme') ?></h2>
            <p class="section-hint">
                <?= __('Applies to everyone who visits the site, players included, not just to you.') ?>
            </p>
        </div>

        <div class="theme-grid">
            <?php foreach ($themes as $slug => $theme): ?>
                <?php $swatch = $theme['swatches']; ?>
                <label class="theme-card<?= $chosen === $slug ? ' is-chosen' : '' ?>">
                    <input type="radio" name="theme" value="<?= h($slug) ?>"
                        <?= $chosen === $slug ? 'checked' : '' ?>>

                    <span class="theme-preview" style="background: <?= h($swatch['bg']) ?>;">
                        <span class="theme-preview-bar" style="background: <?= h($swatch['card']) ?>;">
                            <span class="theme-preview-dot" style="background: <?= h($swatch['accent']) ?>;"></span>
                            <span class="theme-preview-line" style="background: <?= h($swatch['text']) ?>;"></span>
                        </span>
                        <span class="theme-preview-card" style="background: <?= h($swatch['card']) ?>;">
                            <span class="theme-preview-line is-title" style="background: <?= h($swatch['text']) ?>;"></span>
                            <span class="theme-preview-line" style="background: <?= h($swatch['text']) ?>;"></span>
                            <span class="theme-preview-line is-short" style="background: <?= h($swatch['text']) ?>;"></span>
                            <span class="theme-preview-button" style="background: <?= h($swatch['accent']) ?>;"></span>
                        </span>
                    </span>

                    <span class="theme-name">
                        <?= h(__($theme['label'])) ?>
                        <?php if ($theme['dark']): ?>
                            <i class="fas fa-moon theme-dark-mark" title="<?= h(__('A dark theme')) ?>"></i>
                        <?php endif; ?>
                    </span>
                    <span class="theme-blurb"><?= h(__($theme['blurb'])) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="event-form-actions">
        <?= $this->Form->button(
            '<i class="fas fa-save mr-1"></i>' . __('Save theme'),
            ['class' => 'btn btn-primary', 'escapeTitle' => false]
        ) ?>
    </div>

    <?= $this->Form->end() ?>

    <div class="alert alert-info brand-footnote">
        <i class="fas fa-info-circle mr-1"></i>
        <?= __('The previews above are drawn from a handful of colours each. Save a theme and '
            . 'reload to see it applied across the whole site.') ?>
    </div>
</div>

<?php $this->start('script'); ?>
<script>
    // Move the highlight on click rather than waiting for the save to come
    // back, which otherwise reads as the click having done nothing.
    document.querySelectorAll('.theme-card input[type="radio"]').forEach(function (input) {
        input.addEventListener('change', function () {
            document.querySelectorAll('.theme-card').forEach(function (card) {
                var radio = card.querySelector('input[type="radio"]');
                card.classList.toggle('is-chosen', radio !== null && radio.checked);
            });
        });
    });
</script>
<?php $this->end(); ?>
