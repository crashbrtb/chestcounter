<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <?= __('Chests') ?>
    </a>
    <div class="dropdown-menu" aria-labelledby="langDropdown">
        <?= $this->Html->link('Scoreboard', '/score', ['class' => 'dropdown-item']) ?>
        <?= $this->Html->link('Goals', ['controller' => 'StandardChests', 'action' => 'weights'], ['class' => 'dropdown-item']) ?>
    </div>
</li>

<?php
$configTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Config');
$bankFunctionConfig = $configTable->find()
    ->where(['param' => 'bank_function'])
    ->first();
$bankFunctionEnabled = $bankFunctionConfig && (int)$bankFunctionConfig->value === 1;
?>

<?php if ($bankFunctionEnabled): ?>
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="bankDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <?= __('Bank') ?>
    </a>
    <div class="dropdown-menu" aria-labelledby="bankDropdown">
        <?= $this->Html->link('Bank', ['controller' => 'Bank', 'action' => 'index'], ['class' => 'dropdown-item']) ?>
        <?= $this->Html->link('Deposit', ['controller' => 'Bank', 'action' => 'deposit'], ['class' => 'dropdown-item']) ?>
        <?= $this->Html->link('Withdraw', ['controller' => 'Bank', 'action' => 'withdraw'], ['class' => 'dropdown-item']) ?>
        <?= $this->Html->link('Transfer', ['controller' => 'Bank', 'action' => 'transfer'], ['class' => 'dropdown-item']) ?>
        <?= $this->Html->link('Statement', ['controller' => 'Bank', 'action' => 'statement'], ['class' => 'dropdown-item']) ?>
    </div>
</li>
<?php endif; ?>

