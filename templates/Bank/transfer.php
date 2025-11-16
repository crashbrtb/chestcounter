<?php
/**
 * @var \App\View\AppView $this
 * @var array $sourceMembers
 * @var array $destinationMembers
 * @var array $memberBalances
 * @var float $transferFee
 * @var array|null $confirmationData
 */

$hasSource = !empty($sourceMembers);
$hasPositiveBalance = false;
foreach ($memberBalances as $balance) {
    if ($balance > 0) {
        $hasPositiveBalance = true;
        break;
    }
}
?>

<div class="bank transfer content">
    <h3><?= __('Transfer Silver') ?></h3>
    <p><?= __('Choose one of your members as the source, select a destination member, and inform the amount in millions of Silver ($).') ?></p>
    <p><?= __('Transfer fee: {0} $. Fees are charged in addition to the transferred amount.', $this->Number->format($transferFee, ['places' => 0])) ?></p>

    <div class="table-responsive mb-3">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th><?= __('Source member') ?></th>
                    <th><?= __('Balance ($)') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$hasSource): ?>
                    <tr>
                        <td colspan="2"><?= __('No members available.') ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sourceMembers as $memberId => $memberName): ?>
                        <tr>
                            <td><?= h($memberName) ?></td>
                            <td><?= $this->Number->format($memberBalances[$memberId] ?? 0, ['places' => 0]) ?> $</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!$hasSource): ?>
        <div class="alert alert-warning">
            <?= __('You must have at least one member to initiate transfers.') ?>
        </div>
        <?php return; ?>
    <?php endif; ?>

    <?php if (!$hasPositiveBalance): ?>
        <div class="alert alert-danger">
            <?= __('There is no balance available for transfers. The form will be hidden until one member has Silver.') ?>
        </div>
        <?php return; ?>
    <?php endif; ?>

    <?php if ($confirmationData): ?>
        <div class="card border-danger mb-4">
            <div class="card-header bg-danger text-white">
                <?= __('Confirm transfer') ?>
            </div>
            <div class="card-body">
                <p class="text-danger font-weight-bold"><?= __('This operation cannot be undone.') ?></p>
                <ul class="list-unstyled">
                    <li><?= __('From: {0}', h($confirmationData['source']->player)) ?></li>
                    <li><?= __('To: {0}', h($confirmationData['destination']->player)) ?></li>
                    <li><?= __('Amount: {0} $', $this->Number->format($confirmationData['amount'], ['places' => 0])) ?></li>
                    <li><?= __('Fee: {0} $', $this->Number->format($confirmationData['fee'], ['places' => 0])) ?></li>
                    <li><?= __('Total deduction: {0} $', $this->Number->format($confirmationData['totalDeduction'], ['places' => 0])) ?></li>
                    <?php if (!empty($confirmationData['description'])): ?>
                        <li><?= __('Comment: {0}', h($confirmationData['description'])) ?></li>
                    <?php endif; ?>
                </ul>

                <?= $this->Form->create(null) ?>
                <?= $this->Form->hidden('source_member_id', ['value' => $confirmationData['source']->id]) ?>
                <?= $this->Form->hidden('destination_member_id', ['value' => $confirmationData['destination']->id]) ?>
                <?= $this->Form->hidden('amount', ['value' => $confirmationData['amount']]) ?>
                <?= $this->Form->hidden('description', ['value' => $confirmationData['description']]) ?>
                <?= $this->Form->hidden('confirm', ['value' => 1]) ?>
                <?= $this->Form->button(__('Confirm transfer'), ['class' => 'btn btn-danger mr-2']) ?>
                <?= $this->Html->link(__('Cancel'), ['action' => 'transfer'], ['class' => 'btn btn-secondary']) ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
        <?php return; ?>
    <?php endif; ?>

    <?= $this->Form->create(null, ['class' => 'mt-3']) ?>
    <div class="form-group">
        <?= $this->Form->control('source_member_id', [
            'label' => __('Source member'),
            'options' => $sourceMembers,
            'required' => true,
            'class' => 'form-control',
        ]) ?>
    </div>
    <div class="form-group">
        <?= $this->Form->control('destination_member_id', [
            'label' => __('Destination member'),
            'options' => $destinationMembers,
            'required' => true,
            'class' => 'form-control',
        ]) ?>
        <small class="form-text text-muted">
            <?= __('Destination members can belong to you or be unassigned.') ?>
        </small>
    </div>
    <div class="form-group">
        <?= $this->Form->control('amount', [
            'label' => __('Amount (millions of Silver)'),
            'type' => 'number',
            'min' => 1,
            'step' => 1,
            'class' => 'form-control',
            'id' => 'transfer-amount',
            'required' => true,
        ]) ?>
    </div>

    <div class="form-group">
        <?= $this->Form->control('fee_preview', [
            'label' => __('Transfer fee ($)'),
            'type' => 'text',
            'readonly' => true,
            'class' => 'form-control',
            'id' => 'transfer-fee-preview',
            'value' => $this->Number->format($transferFee, ['places' => 0]),
        ]) ?>
    </div>

    <div class="form-group">
        <?= $this->Form->control('total_preview', [
            'label' => __('Total deduction ($)'),
            'type' => 'text',
            'readonly' => true,
            'class' => 'form-control',
            'id' => 'transfer-total-preview',
            'value' => $this->Number->format($transferFee, ['places' => 0]),
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

    <?= $this->Form->button(__('Review transfer'), ['class' => 'btn btn-primary']) ?>
    <?= $this->Form->end() ?>
</div>

<?php $transferFeeValue = (int)$transferFee; ?>
<script>
(function () {
    const amountInput = document.getElementById('transfer-amount');
    if (!amountInput) {
        return;
    }
    const feeField = document.getElementById('transfer-fee-preview');
    const totalField = document.getElementById('transfer-total-preview');
    const fee = <?= json_encode($transferFeeValue) ?>;

    const recalc = () => {
        const amount = parseInt(amountInput.value, 10) || 0;
        const total = amount + fee;
        feeField.value = String(fee);
        totalField.value = String(total);
    };

    amountInput.addEventListener('input', recalc);
    recalc();
})();
</script>

