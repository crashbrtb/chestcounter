<?php
/**
 * @var \App\View\AppView $this
 * @var array $ownMembers
 * @var array $allMembers
 * @var bool $isAdmin
 * @var float $depositFee
 * @var float $caravanFee
 */

$selectOptions = $ownMembers;
if ($isAdmin) {
    $otherMembers = array_diff_key($allMembers, $ownMembers);
    $selectOptions = [
        __('My members') => $ownMembers,
    ];
    if (!empty($otherMembers)) {
        $selectOptions[__('All members')] = $otherMembers;
    }
}
?>

<div class="bank deposit content">
    <h3><?= __('Register Deposit') ?></h3>
    <p><?= __('Fill the form below to request a Silver deposit. Amounts must be integers representing millions of Silver ($).') ?></p>
    <p><?= __('Current flat deposit fee: {0} $. Caravan fee: {1}%.', $this->Number->format($depositFee, ['places' => 0]), $this->Number->format($caravanFee, ['places' => 0])) ?></p>
    <p class="text-muted"><small><?= __('Note: When caravan is selected, the deposit fee is set to 0 and only the caravan fee applies.') ?></small></p>

    <?= $this->Form->create(null, ['class' => 'mt-3']) ?>
    <div class="form-group">
        <?= $this->Form->control('member_id', [
            'label' => __('Member'),
            'options' => $selectOptions,
            'class' => 'form-control',
            'required' => true,
        ]) ?>
    </div>
    <div class="form-group">
        <?= $this->Form->control('amount', [
            'label' => __('Amount (millions of Silver)'),
            'type' => 'number',
            'min' => 1,
            'step' => 1,
            'id' => 'deposit-amount',
            'class' => 'form-control',
            'required' => true,
        ]) ?>
        <small class="form-text text-muted">
            <?= __('Example: 10 means 10 million Silver ($). Only integers are accepted.') ?>
        </small>
    </div>

    <div class="form-check mb-3">
        <?= $this->Form->checkbox('caravan', ['class' => 'form-check-input', 'id' => 'deposit-caravan']) ?>
        <?= $this->Form->label('deposit-caravan', __('Caravan (apply caravan fee)'), ['class' => 'form-check-label']) ?>
    </div>

    <div class="form-group">
        <?= $this->Form->control('fee_preview', [
            'label' => __('Estimated total fee ($)'),
            'type' => 'text',
            'readonly' => true,
            'class' => 'form-control',
            'id' => 'deposit-fee-preview',
            'value' => $this->Number->format(0, ['places' => 0]),
        ]) ?>
    </div>

    <div class="form-group caravan-field d-none">
        <?= $this->Form->control('caravan_preview', [
            'label' => __('Amount after caravan fee ($)'),
            'type' => 'text',
            'readonly' => true,
            'class' => 'form-control',
            'id' => 'deposit-caravan-preview',
            'value' => $this->Number->format(0, ['places' => 0]),
        ]) ?>
    </div>

    <div class="form-group">
        <?= $this->Form->control('final_preview', [
            'label' => __('Net amount to credit ($)'),
            'type' => 'text',
            'readonly' => true,
            'class' => 'form-control',
            'id' => 'deposit-final-preview',
            'value' => $this->Number->format(0, ['places' => 0]),
        ]) ?>
    </div>

    <div class="form-group">
        <?= $this->Form->control('description', [
            'label' => __('Comment (English only, optional)'),
            'type' => 'text',
            'maxlength' => 512,
            'class' => 'form-control',
            'placeholder' => __('Short English comment (max 512 characters)'),
        ]) ?>
    </div>

    <div class="alert alert-info">
        <?= $isAdmin
            ? __('Administrator deposits are approved instantly.')
            : __('Deposits created by players stay pending until an administrator approves them.') ?>
    </div>

    <?= $this->Form->button(__('Submit deposit'), ['class' => 'btn btn-success']) ?>
    <?= $this->Form->end() ?>
</div>

<?php
$depositFeeValue = (int)$depositFee;
$caravanFeeValue = (int)$caravanFee;
?>

<script>
(function () {
    const amountInput = document.getElementById('deposit-amount');
    const caravanCheckbox = document.getElementById('deposit-caravan');
    const feeField = document.getElementById('deposit-fee-preview');
    const finalField = document.getElementById('deposit-final-preview');
    const caravanFieldWrapper = document.querySelector('.caravan-field');
    const caravanPreview = document.getElementById('deposit-caravan-preview');
    const depositFee = <?= json_encode($depositFeeValue) ?>;
    const caravanPercent = <?= json_encode($caravanFeeValue) ?>;

    const recalc = () => {
        const amount = parseInt(amountInput.value, 10) || 0;
        // If caravan is selected, deposit fee is 0
        const actualDepositFee = caravanCheckbox.checked ? 0 : depositFee;
        const caravanFeeValue = caravanCheckbox.checked ? Math.floor(amount * (caravanPercent / 100)) : 0;
        const totalFee = actualDepositFee + caravanFeeValue;
        let caravanNet = amount - caravanFeeValue;
        if (caravanNet < 0) {
            caravanNet = 0;
        }
        let finalAmount = amount - totalFee;
        if (finalAmount < 0) {
            finalAmount = 0;
        }

        feeField.value = String(totalFee);
        finalField.value = String(finalAmount);

        if (caravanCheckbox.checked) {
            caravanFieldWrapper.classList.remove('d-none');
            caravanPreview.value = String(caravanNet);
        } else {
            caravanFieldWrapper.classList.add('d-none');
        }
    };

    amountInput.addEventListener('input', recalc);
    caravanCheckbox.addEventListener('change', recalc);
    recalc();
})();
</script>

