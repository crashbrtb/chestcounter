<?php
/**
 * @var \App\View\AppView $this
 * @var list<\App\Model\Entity\Troop> $troops
 * @var list<string> $groups
 * @var array<string, mixed> $form
 * @var \App\Service\Stacker\StackPlan|null $plan
 * @var array<string, string> $orderPresets
 */

use App\Service\Stacker\StackRequest;

$this->assign('title', __('Troop Calculator'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('Troop Calculator')],
]);

/**
 * Read a remembered form value.
 */
$val = function (string $key, $default = '') use ($form) {
    $value = $form[$key] ?? null;

    return $value === null || $value === '' ? $default : $value;
};

/**
 * Read a remembered value from one of the bonus grids.
 */
$bonus = function (string $group, string $key) use ($form) {
    $value = $form[$group][$key] ?? null;

    return $value === null || $value === '' ? '' : $value;
};

$selectedGroups = (array)($form['groups'] ?? []);
$excluded = (array)($form['excluded'] ?? []);
$isFirstVisit = $form === [];

$classLabels = [
    'guardsman' => __('Guardsmen'),
    'specialist' => __('Specialists'),
    'monster' => __('Monsters'),
    'engineer' => __('Engineer corps'),
    'epic_monster_hunter' => __('Epic Monster Hunters'),
];
$categoryLabels = [
    'melee' => __('Melee'),
    'mounted' => __('Mounted'),
    'ranged' => __('Ranged'),
    'flying' => __('Flying'),
];
$typeLabels = [
    'beast' => __('Beasts'),
    'giant' => __('Giants'),
    'dragon' => __('Dragons'),
    'elemental' => __('Elementals'),
];
$sectionLabels = [
    'army' => __('Army (leadership)'),
    'monsters' => __('Monsters (dominance)'),
    'mercenaries' => __('Mercenaries (authority)'),
];

/**
 * Compact big numbers so the result tables stay readable.
 */
$short = function (float $number): string {
    foreach ([['B', 1e9], ['M', 1e6], ['K', 1e3]] as [$suffix, $size]) {
        if (abs($number) >= $size) {
            return number_format($number / $size, 2) . $suffix;
        }
    }

    return number_format($number);
};
?>

<?= $this->Form->create(null, ['url' => ['action' => 'index'], 'id' => 'calc-form']) ?>

<div class="row">
    <div class="col-12 col-xl-5">

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-users mr-1 text-muted"></i> <?= __('Army Limits') ?></h2>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    <?= __('Take these from the army screen. Each limit is spent by its own section: leadership buys guardsmen, specialists and siege; dominance buys monsters; authority buys mercenaries.') ?>
                </p>
                <div class="form-row">
                    <div class="col-4">
                        <?= $this->Form->control('leadership_cap', [
                            'type' => 'text',
                            'label' => __('Leadership'),
                            'value' => $val('leadership_cap'),
                            'placeholder' => '1000000',
                            'class' => 'form-control form-control-sm',
                        ]) ?>
                    </div>
                    <div class="col-4">
                        <?= $this->Form->control('dominance_cap', [
                            'type' => 'text',
                            'label' => __('Dominance'),
                            'value' => $val('dominance_cap'),
                            'placeholder' => '4000',
                            'class' => 'form-control form-control-sm',
                        ]) ?>
                    </div>
                    <div class="col-4">
                        <?= $this->Form->control('authority_cap', [
                            'type' => 'text',
                            'label' => __('Authority'),
                            'value' => $val('authority_cap'),
                            'placeholder' => '3000',
                            'class' => 'form-control form-control-sm',
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-flask mr-1 text-muted"></i> <?= __('Bonuses') ?></h2>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    <?= __('Percentages exactly as the game reports them: type 420 for +420%. Leave a field blank when you have no bonus of that kind.') ?>
                </p>

                <h6 class="text-uppercase text-muted small mb-2"><?= __('By class') ?></h6>
                <table class="table table-sm mb-3">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="text-center"><?= __('Health %') ?></th>
                            <th class="text-center"><?= __('Strength %') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($classLabels as $key => $label) : ?>
                        <tr>
                            <td class="align-middle"><?= h($label) ?></td>
                            <td><?= $this->Form->number("health_class.$key", [
                                'step' => 'any',
                                'value' => $bonus('health_class', $key),
                                'class' => 'form-control form-control-sm text-right',
                                'label' => false,
                            ]) ?></td>
                            <td><?= $this->Form->number("strength_class.$key", [
                                'step' => 'any',
                                'value' => $bonus('strength_class', $key),
                                'class' => 'form-control form-control-sm text-right',
                                'label' => false,
                            ]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <h6 class="text-uppercase text-muted small mb-2"><?= __('By category') ?></h6>
                <table class="table table-sm mb-3">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="text-center"><?= __('Health %') ?></th>
                            <th class="text-center"><?= __('Strength %') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($categoryLabels as $key => $label) : ?>
                        <tr>
                            <td class="align-middle"><?= h($label) ?></td>
                            <td><?= $this->Form->number("health_category.$key", [
                                'step' => 'any',
                                'value' => $bonus('health_category', $key),
                                'class' => 'form-control form-control-sm text-right',
                                'label' => false,
                            ]) ?></td>
                            <td><?= $this->Form->number("strength_category.$key", [
                                'step' => 'any',
                                'value' => $bonus('strength_category', $key),
                                'class' => 'form-control form-control-sm text-right',
                                'label' => false,
                            ]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <h6 class="text-uppercase text-muted small mb-2"><?= __('Monsters Boost research') ?></h6>
                <table class="table table-sm mb-2">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="text-center"><?= __('Health %') ?></th>
                            <th class="text-center"><?= __('Strength %') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($typeLabels as $key => $label) : ?>
                        <tr>
                            <td class="align-middle"><?= h($label) ?></td>
                            <td><?= $this->Form->number("health_type.$key", [
                                'step' => 'any',
                                'value' => $bonus('health_type', $key),
                                'class' => 'form-control form-control-sm text-right',
                                'label' => false,
                            ]) ?></td>
                            <td><?= $this->Form->number("strength_type.$key", [
                                'step' => 'any',
                                'value' => $bonus('strength_type', $key),
                                'class' => 'form-control form-control-sm text-right',
                                'label' => false,
                            ]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="form-group">
                    <?= $this->Form->control('strength_vs_epic_monsters', [
                        'type' => 'number',
                        'step' => 'any',
                        'label' => __('Strength against Epic Monsters (%)'),
                        'value' => $val('strength_vs_epic_monsters'),
                        'class' => 'form-control form-control-sm',
                    ]) ?>
                    <small class="form-text text-muted">
                        <?= __('The single figure the battle report shows for every unit in the march.') ?>
                    </small>
                </div>

                <div class="custom-control custom-checkbox">
                    <?= $this->Form->checkbox('monster_bonus_includes_lowest_type', [
                        'checked' => $isFirstVisit || (bool)($form['monster_bonus_includes_lowest_type'] ?? false),
                        'class' => 'custom-control-input',
                        'id' => 'monster-includes-lowest',
                        'hiddenField' => true,
                    ]) ?>
                    <label class="custom-control-label" for="monster-includes-lowest">
                        <?= __('The monster figures above already include the weakest Monsters Boost research') ?>
                    </label>
                    <small class="form-text text-muted">
                        <?= __('This is how the army screen reports it. Untick only if you typed the monster bonuses with no type research folded in, otherwise the boost is counted twice.') ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-7">

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-sort-amount-down mr-1 text-muted"></i> <?= __('Kill Order') ?></h2>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    <?= __('The stacks are lost in this order, weakest first, so the strongest units survive the most rounds and land the most hits.') ?>
                </p>

                <div class="form-group">
                    <label><?= __('Levels you are bringing') ?></label>
                    <div class="border rounded p-2" style="max-height: 190px; overflow-y: auto;">
                        <?php foreach ($groups as $code) : ?>
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" class="custom-control-input" name="groups[]"
                                       id="group-<?= h($code) ?>" value="<?= h($code) ?>"
                                    <?= in_array($code, $selectedGroups, true) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="group-<?= h($code) ?>"><?= h($code) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <small class="form-text text-muted">
                        <?= __('E = engineer corps, S = specialists, G = guardsmen, M = monsters. Leave all unticked to use everything in the catalogue.') ?>
                    </small>
                </div>

                <div class="form-row">
                    <div class="col-12 col-md-6">
                        <?= $this->Form->control('order_preset', [
                            'type' => 'select',
                            'label' => __('Order within each level'),
                            'options' => $orderPresets,
                            'value' => $val('order_preset', 'specialists_first'),
                            'class' => 'form-control form-control-sm',
                        ]) ?>
                    </div>
                    <div class="col-12 col-md-6">
                        <?= $this->Form->control('custom_order', [
                            'type' => 'text',
                            'label' => __('Custom order'),
                            'value' => $val('custom_order'),
                            'placeholder' => 'E9, S8, G8, S9, G9, M8, M9',
                            'class' => 'form-control form-control-sm',
                        ]) ?>
                    </div>
                </div>

                <div class="form-group">
                    <label><?= __('Category order within a level') ?></label>
                    <div class="form-row">
                        <?php
                        $currentCategoryOrder = (array)($form['category_order'] ?? StackRequest::DEFAULT_CATEGORY_ORDER);
                        foreach (array_keys($categoryLabels) as $position => $ignored) :
                            $value = $currentCategoryOrder[$position] ?? array_keys($categoryLabels)[$position];
                            ?>
                            <div class="col-6 col-md-3">
                                <?= $this->Form->select("category_order[$position]", $categoryLabels, [
                                    'value' => $value,
                                    'class' => 'form-control form-control-sm',
                                ]) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <small class="form-text text-muted">
                        <?= __('First box dies first. Siege engines always lead their own level.') ?>
                    </small>
                </div>
            </div>
        </div>

        <div class="card card-secondary card-outline collapsed-card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-sliders-h mr-1 text-muted"></i> <?= __('Advanced') ?></h2>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="col-12 col-md-4">
                        <?= $this->Form->control('enemy_stack_count', [
                            'type' => 'number',
                            'label' => __('Enemy stacks'),
                            'value' => $val('enemy_stack_count', 4),
                            'min' => 0,
                            'max' => 12,
                            'class' => 'form-control form-control-sm',
                        ]) ?>
                        <small class="form-text text-muted">
                            <?= __('4 for a standard Epic Monster. The first stack lost in each round is a sacrifice that will not strike. Use 0 to treat every stack as a striker.') ?>
                        </small>
                    </div>
                    <div class="col-12 col-md-4">
                        <?= $this->Form->control('section_gap', [
                            'type' => 'number',
                            'step' => '0.01',
                            'min' => '0.5',
                            'max' => '1',
                            'label' => __('Section gap'),
                            'value' => $val('section_gap', '0.95'),
                            'class' => 'form-control form-control-sm',
                        ]) ?>
                        <small class="form-text text-muted">
                            <?= __('How far below the army the monsters start, and the mercenaries below them. 0.95 leaves a 5% gap.') ?>
                        </small>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="custom-control custom-checkbox mt-4">
                            <?= $this->Form->checkbox('enforce_strike_order', [
                                'checked' => $isFirstVisit || (bool)($form['enforce_strike_order'] ?? false),
                                'class' => 'custom-control-input',
                                'id' => 'enforce-strike-order',
                                'hiddenField' => true,
                            ]) ?>
                            <label class="custom-control-label" for="enforce-strike-order">
                                <?= __('Keep strength descending too') ?>
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            <?= __('Stacks strike in order of strength but die in order of health. Leave this on so every stack strikes before it is lost.') ?>
                        </small>
                    </div>
                </div>

                <div class="form-group mt-2">
                    <label for="excluded"><?= __('Units to leave out') ?></label>
                    <select name="excluded[]" id="excluded" class="form-control form-control-sm" multiple size="8">
                        <?php foreach ($troops as $troop) : ?>
                            <option value="<?= h($troop->slug) ?>"
                                <?= in_array($troop->slug, $excluded, true) ? 'selected' : '' ?>>
                                <?= h($troop->group_code) ?> &middot; <?= h($troop->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">
                        <?= __('Hold Ctrl to pick several. Use this for units you have not researched yet.') ?>
                    </small>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <?= $this->Form->button(__('Calculate'), ['class' => 'btn btn-primary', 'type' => 'submit']) ?>
            <?= $this->Html->link(
                __('Clear saved inputs'),
                ['action' => 'reset'],
                ['class' => 'btn btn-outline-secondary', 'method' => 'post', 'confirm' => __('Clear the remembered inputs?')]
            ) ?>
        </div>
    </div>
</div>

<?= $this->Form->end() ?>

<?php if ($plan !== null && $plan->lines() !== []) : ?>
    <?php foreach ($plan->warnings() as $warning) : ?>
        <div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-1"></i> <?= h($warning) ?></div>
    <?php endforeach; ?>

    <div class="card card-success card-outline">
        <div class="card-header d-flex flex-column flex-md-row">
            <h2 class="card-title"><i class="fas fa-list-ol mr-1 text-muted"></i> <?= __('Planned March') ?></h2>
            <div class="ml-auto">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="copy-plan">
                    <i class="far fa-copy mr-1"></i><?= __('Copy as text') ?>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-6 col-md-3">
                    <span class="text-muted small d-block"><?= __('Total health') ?></span>
                    <strong><?= h($short($plan->totalHealth())) ?></strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="text-muted small d-block"><?= __('Total strength') ?></span>
                    <strong><?= h($short($plan->totalStrength())) ?></strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="text-muted small d-block"><?= __('Stacks') ?></span>
                    <strong><?= count($plan->lines()) ?></strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="text-muted small d-block"><?= __('Full revival') ?></span>
                    <strong><?= number_format($plan->totalRevivalGold()) ?> <?= __('gold') ?></strong>
                </div>
            </div>

            <?php foreach ($plan->sections() as $section => $lines) : ?>
                <?php if ($lines === []) {
                    continue;
                } ?>
                <h6 class="text-uppercase text-muted small mt-3 mb-2">
                    <?= h($sectionLabels[$section] ?? $section) ?>
                    &mdash;
                    <?= number_format($plan->usedCap($section)) ?> / <?= number_format($plan->offeredCap($section)) ?>
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="width: 3rem;">#</th>
                                <th><?= __('Unit') ?></th>
                                <th style="width: 4rem;"><?= __('Tier') ?></th>
                                <th class="text-right"><?= __('Count') ?></th>
                                <th class="text-right"><?= __('Stack health') ?></th>
                                <th class="text-right"><?= __('Stack strength') ?></th>
                                <th class="text-right"><?= __('Limit used') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lines as $line) : ?>
                            <tr<?= $line->isSacrifice ? ' class="table-warning"' : '' ?>>
                                <td><?= $line->rank + 1 ?></td>
                                <td>
                                    <?= h($line->troop->name) ?>
                                    <?php if ($line->isSacrifice) : ?>
                                        <span class="badge badge-warning ml-1" title="<?= h(__('Opens a round and will be lost before it strikes')) ?>">
                                            <?= __('sacrifice') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= h($line->troop->group_code) ?></small></td>
                                <td class="text-right"><strong><?= number_format($line->count) ?></strong></td>
                                <td class="text-right"><?= h($short($line->stackHealth())) ?></td>
                                <td class="text-right"><?= h($short($line->stackStrength())) ?></td>
                                <td class="text-right text-muted"><?= number_format($line->costUsed()) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php
    $copyLines = [];
    foreach ($plan->sections() as $section => $lines) {
        if ($lines === []) {
            continue;
        }
        $copyLines[] = strtoupper((string)($sectionLabels[$section] ?? $section));
        foreach ($lines as $line) {
            $copyLines[] = sprintf(
                '%d. %s (%s): %s',
                $line->rank + 1,
                $line->troop->name,
                $line->troop->group_code,
                number_format($line->count)
            );
        }
        $copyLines[] = '';
    }
    ?>
    <script>
        document.getElementById('copy-plan')?.addEventListener('click', function () {
            var text = <?= json_encode(implode("\n", $copyLines)) ?>;
            navigator.clipboard.writeText(text).then(function () {
                var button = document.getElementById('copy-plan');
                var original = button.innerHTML;
                button.innerHTML = '<?= h(__('Copied')) ?>';
                setTimeout(function () { button.innerHTML = original; }, 1500);
            });
        });
    </script>
<?php endif; ?>
