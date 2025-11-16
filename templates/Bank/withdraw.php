<?php
/**
 * @var \App\View\AppView $this
 * @var array $allMembers
 * @var array $memberBalances
 * @var float $withdrawFee
 * @var bool $isAdmin
 */

$hasMembers = !empty($allMembers);
$hasPositiveBalance = false;
foreach ($memberBalances as $balance) {
    if ($balance > 0) {
        $hasPositiveBalance = true;
        break;
    }
}
?>

<div class="bank withdraw content">
    <h3><?= __('Request Withdrawal') ?></h3>
    <p><?= __('Select a member and enter the Silver amount you want to withdraw. All values represent millions of Silver ($).') ?></p>
    <p><?= __('Current withdrawal fee: {0} $.', $this->Number->format($withdrawFee, ['places' => 0])) ?></p>


    <?php if (!$hasMembers): ?>
        <div class="alert alert-warning">
            <?= __('You do not have members available for withdrawals.') ?>
        </div>
        <?php return; ?>
    <?php endif; ?>

    <div class="table-responsive mb-3">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th><?= __('Member') ?></th>
                    <th><?= __('Current balance ($)') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allMembers as $memberId => $memberName): ?>
                    <tr>
                        <td><?= h($memberName) ?></td>
                        <td><?= $this->Number->format($memberBalances[$memberId] ?? 0, ['places' => 0]) ?> $</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (!$hasPositiveBalance): ?>
        <div class="alert alert-danger">
            <?= __('All balances are zero or negative. Withdraw buttons are hidden until there is enough Silver.') ?>
        </div>
        <?php return; ?>
    <?php endif; ?>

    <?= $this->Form->create(null, ['class' => 'mt-3']) ?>
    <div class="form-group">
        <?= $this->Form->control('member_id', [
            'label' => __('Member'),
            'options' => $allMembers,
            'required' => true,
            'class' => 'form-control',
        ]) ?>
    </div>
    <div class="form-group">
        <?= $this->Form->control('amount', [
            'label' => __('Amount (millions of Silver)'),
            'type' => 'number',
            'min' => 1,
            'step' => 1,
            'class' => 'form-control',
            'id' => 'withdraw-amount',
            'required' => true,
        ]) ?>
        <small class="form-text text-muted">
            <?= __('Only positive integers are accepted.') ?>
        </small>
    </div>

    <div class="form-group">
        <?= $this->Form->control('fee_preview', [
            'label' => __('Withdrawal fee ($)'),
            'type' => 'text',
            'readonly' => true,
            'class' => 'form-control',
            'id' => 'withdraw-fee-preview',
            'value' => $this->Number->format($withdrawFee, ['places' => 0]),
        ]) ?>
    </div>

    <div class="form-group">
        <?= $this->Form->control('final_preview', [
            'label' => __('Total deduction ($)'),
            'type' => 'text',
            'readonly' => true,
            'class' => 'form-control',
            'id' => 'withdraw-total-preview',
            'value' => $this->Number->format($withdrawFee, ['places' => 0]),
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
            ? __('Administrator withdrawals are executed immediately.')
            : __('Withdrawals stay pending until an administrator approves them.') ?>
    </div>

    <?= $this->Form->button(__('Submit withdrawal'), ['class' => 'btn btn-warning']) ?>
    <?= $this->Form->end() ?>
</div>

<?php $withdrawFeeValue = (int)$withdrawFee; ?>
<script>
(function () {
    const amountInput = document.getElementById('withdraw-amount');
    const feeField = document.getElementById('withdraw-fee-preview');
    const totalField = document.getElementById('withdraw-total-preview');
    const fee = <?= json_encode($withdrawFeeValue) ?>;

    const recalc = () => {
        const amount = parseInt(amountInput.value, 10) || 0;
        const total = amount + fee;
        feeField.value = String(fee);
        totalField.value = String(total);
    };

    amountInput.addEventListener('input', recalc);
    recalc();
})();

// Highlight error messages with extra emphasis
(function() {
    const errorAlerts = document.querySelectorAll('.alert-danger, .alert.alert-danger');
    errorAlerts.forEach(function(alert) {
        // Apply enhanced styling
        alert.style.fontSize = '1.2em';
        alert.style.fontWeight = 'bold';
        alert.style.border = '4px solid #dc0000';
        alert.style.borderRadius = '8px';
        alert.style.padding = '20px 25px';
        alert.style.marginBottom = '30px';
        alert.style.backgroundColor = '#ffe6e6';
        alert.style.boxShadow = '0 6px 12px rgba(220, 0, 0, 0.5)';
        alert.style.color = '#dc0000';
        
        // Convert line breaks to HTML breaks
        const textNodes = [];
        function getTextNodes(node) {
            if (node.nodeType === 3) { // Text node
                textNodes.push(node);
            } else {
                for (let child of node.childNodes) {
                    getTextNodes(child);
                }
            }
        }
        getTextNodes(alert);
        
        textNodes.forEach(function(textNode) {
            const text = textNode.textContent;
            if (text.includes('\n')) {
                const parent = textNode.parentNode;
                const parts = text.split('\n');
                parts.forEach(function(part, index) {
                    if (index > 0) {
                        parent.insertBefore(document.createElement('br'), textNode);
                    }
                    if (part.trim()) {
                        const span = document.createElement('span');
                        span.textContent = part;
                        span.style.display = 'block';
                        span.style.marginBottom = '5px';
                        if (part.includes('⚠️') || part.includes('INSUFFICIENT')) {
                            span.style.fontSize = '1.3em';
                            span.style.color = '#dc0000';
                        }
                        parent.insertBefore(span, textNode);
                    }
                });
                parent.removeChild(textNode);
            }
        });
        
        // Add icon if not present
        if (!alert.querySelector('.fas, .fa')) {
            const icon = document.createElement('i');
            icon.className = 'fas fa-exclamation-triangle';
            icon.style.marginRight = '12px';
            icon.style.fontSize = '1.4em';
            icon.style.color = '#dc0000';
            alert.insertBefore(icon, alert.firstChild);
        }
        
        // Make all text more visible
        const textElements = alert.querySelectorAll('p, div, span');
        textElements.forEach(function(el) {
            if (el.textContent.trim() && !el.querySelector('.fas, .fa')) {
                el.style.fontSize = '1.1em';
                el.style.lineHeight = '1.6';
                el.style.color = '#dc0000';
            }
        });
    });
})();
</script>

