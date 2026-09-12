<?php
/**
 * Choose the site's logo and favicon.
 *
 * The two slots are independent on purpose: a busy emblem can read perfectly
 * well in the navbar and turn to mush in a 16-pixel browser tab, so an
 * administrator is free to pair a detailed logo with a simpler icon.
 *
 * @var \App\View\AppView $this
 * @var \App\Service\BrandingService $branding
 * @var array<string, array{label: string, blurb: string}> $presets
 * @var string $logoSlug
 * @var string $faviconSlug
 * @var bool $hasCustomLogo
 * @var bool $hasCustomFavicon
 */

use App\Service\BrandingService;

$this->assign('title', __('Branding'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('Config'), 'url' => ['action' => 'index']],
    ['title' => __('Branding')],
]);

$this->Html->css('branding', ['block' => 'css']);

/**
 * Sizes as a person would write them: "2 MB" rather than "2,048 KB".
 */
$fileSize = function (int $bytes): string {
    if ($bytes >= 1048576) {
        return rtrim(rtrim(number_format($bytes / 1048576, 1, '.', ''), '0'), '.') . ' MB';
    }

    return number_format($bytes / 1024, 0, '.', '') . ' KB';
};
?>
<div class="content-page-wrap branding-page">

    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-shield-alt text-primary"></i> <?= __('Branding') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('The logo in the navbar and the icon in the browser tab') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-sliders-h mr-1"></i>' . __('All settings'),
                ['action' => 'index'],
                ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <?= $this->Form->create(null, ['type' => 'file']) ?>

    <?php
    $sections = [
        'logo' => [
            'title' => __('Logo'),
            'icon' => 'fa-flag',
            'hint' => __('Shown beside the site name in the navbar, and above the sign-in box.'),
            'chosen' => $logoSlug,
            'hasCustom' => $hasCustomLogo,
            'kind' => 'logo',
            'accept' => 'image/png,image/jpeg,image/gif,image/webp',
            'rules' => [
                __('PNG, JPEG, GIF or WebP. Use a PNG if you want a transparent background.'),
                __(
                    'Between {0}x{0} and {1}x{1} pixels.',
                    BrandingService::LOGO_MIN_SIDE,
                    BrandingService::LOGO_MAX_SIDE
                ),
                __(
                    'Square, or up to {0} times wider than it is tall. Taller than it is wide is not accepted.',
                    (int)BrandingService::LOGO_MAX_RATIO
                ),
                __('At most {0}.', $fileSize(BrandingService::LOGO_MAX_BYTES)),
                __(
                    'Saved as a PNG {0} pixels on its longest side, in webroot/img/branding/custom.',
                    BrandingService::LOGO_STORED_SIDE
                ),
            ],
        ],
        'favicon' => [
            'title' => __('Favicon'),
            'icon' => 'fa-window-maximize',
            'hint' => __('Shown in the browser tab, in bookmarks, and when somebody pins the site.'),
            'chosen' => $faviconSlug,
            'hasCustom' => $hasCustomFavicon,
            'kind' => 'favicon',
            'accept' => 'image/png,image/jpeg,image/gif,image/webp,image/x-icon,.ico',
            'rules' => [
                __('PNG, JPEG, GIF, WebP or ICO. Anything but JPEG can keep a transparent background.'),
                __(
                    'Between {0}x{0} and {1}x{1} pixels.',
                    BrandingService::FAVICON_MIN_SIDE,
                    BrandingService::FAVICON_MAX_SIDE
                ),
                __(
                    'Square, give or take {0}%. Keep the artwork simple: it is usually drawn at 16 pixels.',
                    (int)(BrandingService::FAVICON_RATIO_TOLERANCE * 100)
                ),
                __('At most {0}.', $fileSize(BrandingService::FAVICON_MAX_BYTES)),
                __(
                    'Saved as a {0}x{0} PNG plus a generated favicon.ico holding the 16, 32, 48 and 64 pixel sizes.',
                    BrandingService::FAVICON_STORED_SIDE
                ),
            ],
        ],
    ];
    ?>

    <?php foreach ($sections as $name => $section): ?>
        <div class="brand-section">
            <div class="brand-section-head">
                <h2><i class="fas <?= h($section['icon']) ?> text-primary"></i> <?= h($section['title']) ?></h2>
                <p class="section-hint"><?= h($section['hint']) ?></p>
            </div>

            <div class="brand-grid">
                <?php
                // Always rendered, even before anything has been uploaded: it
                // is the slot the file picker previews into, and an empty one
                // is what tells an administrator that the slot exists at all.
                $customUrl = $section['hasCustom']
                    ? $branding->previewUrl(BrandingService::CUSTOM, $section['kind'])
                    : null;
                ?>
                <label class="brand-card brand-card-custom<?= $section['chosen'] === BrandingService::CUSTOM ? ' is-chosen' : '' ?><?= $section['hasCustom'] ? '' : ' is-empty' ?>">
                    <input type="radio" name="<?= h($name) ?>" value="<?= h(BrandingService::CUSTOM) ?>"
                        <?= $section['chosen'] === BrandingService::CUSTOM ? 'checked' : '' ?>
                        <?= $section['hasCustom'] ? '' : 'disabled' ?>>
                    <span class="brand-thumb">
                        <img src="<?= $customUrl !== null ? $this->Url->assetUrl($customUrl) : '' ?>" alt=""
                             <?= $customUrl === null ? 'hidden' : '' ?>>
                        <?php if ($customUrl === null): ?>
                            <i class="fas fa-cloud-upload-alt text-muted"></i>
                        <?php endif; ?>
                    </span>
                    <span class="brand-name"><?= __('Your upload') ?></span>
                    <span class="brand-blurb">
                        <?= $section['hasCustom']
                            ? __('The image you uploaded.')
                            : __('Nothing uploaded yet.') ?>
                    </span>
                </label>

                <?php foreach ($presets as $slug => $preset): ?>
                    <?php $url = $branding->previewUrl($slug, $section['kind']); ?>
                    <label class="brand-card<?= $section['chosen'] === $slug ? ' is-chosen' : '' ?>">
                        <input type="radio" name="<?= h($name) ?>" value="<?= h($slug) ?>"
                            <?= $section['chosen'] === $slug ? 'checked' : '' ?>>
                        <span class="brand-thumb">
                            <?php if ($url !== null): ?>
                                <img src="<?= $this->Url->assetUrl($url) ?>" alt="">
                            <?php else: ?>
                                <i class="fas fa-image text-muted"></i>
                            <?php endif; ?>
                        </span>
                        <span class="brand-name"><?= h(__($preset['label'])) ?></span>
                        <span class="brand-blurb"><?= h(__($preset['blurb'])) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="brand-upload">
                <label for="<?= h($name) ?>-file">
                    <i class="fas fa-upload mr-1"></i><?= __('Or upload your own {0}', mb_strtolower($section['title'])) ?>
                </label>
                <input type="file" name="<?= h($name) ?>_file" id="<?= h($name) ?>-file"
                       class="form-control-file" accept="<?= h($section['accept']) ?>">
                <p class="brand-upload-note">
                    <?= __('Uploading replaces whatever is in the "Your upload" slot and selects it.') ?>
                </p>
                <ul class="brand-rules">
                    <?php foreach ($section['rules'] as $rule): ?>
                        <li><?= h($rule) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="event-form-actions">
        <?= $this->Form->button(
            '<i class="fas fa-save mr-1"></i>' . __('Save branding'),
            ['class' => 'btn btn-primary', 'escapeTitle' => false]
        ) ?>
    </div>

    <?= $this->Form->end() ?>

    <div class="alert alert-info brand-footnote">
        <i class="fas fa-info-circle mr-1"></i>
        <?= __('Every image is stored as a file under webroot/img/branding, never in the database. '
            . 'Browsers cache icons hard, so a changed favicon can take a reload or two to appear in the tab.') ?>
    </div>
</div>

<?php $this->start('script'); ?>
<script>
    // Move the highlight as soon as a card is clicked: waiting for the save to
    // come back leaves the page looking like the click did nothing.
    document.querySelectorAll('.brand-card input[type="radio"]').forEach(function (input) {
        input.addEventListener('change', function () {
            document.querySelectorAll('.brand-card input[name="' + input.name + '"]').forEach(function (other) {
                other.closest('.brand-card').classList.toggle('is-chosen', other.checked);
            });
        });
    });

    // Preview the chosen file in its own card, so an upload can be checked
    // against the presets before it is saved.
    document.querySelectorAll('.brand-upload input[type="file"]').forEach(function (input) {
        input.addEventListener('change', function () {
            if (!input.files || !input.files[0]) {
                return;
            }
            var section = input.closest('.brand-section');
            var card = section.querySelector('.brand-card-custom');
            if (!card) {
                return;
            }
            var reader = new FileReader();
            reader.onload = function (event) {
                var img = card.querySelector('img');
                if (img) {
                    img.src = event.target.result;
                    img.hidden = false;
                }
                var placeholder = card.querySelector('.brand-thumb i');
                if (placeholder) {
                    placeholder.hidden = true;
                }
                card.classList.remove('is-empty');
                var radio = card.querySelector('input[type="radio"]');
                if (radio) {
                    // It starts disabled while the slot is empty, and an
                    // upload is what fills it.
                    radio.disabled = false;
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                }
            };
            reader.readAsDataURL(input.files[0]);
        });
    });
</script>
<?php $this->end(); ?>
