<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <?= __('Chests') ?>
    </a>
    <div class="dropdown-menu" aria-labelledby="langDropdown">
        <?= $this->Html->link('Scoreboard', '/score', ['class' => 'dropdown-item']) ?>
        <?= $this->Html->link('Goals', ['controller' => 'StandardChests', 'action' => 'weights'], ['class' => 'dropdown-item']) ?>
        <?= $this->Html->link('History', '/pages/underconstruction', ['class' => 'dropdown-item']) ?>
        
    </div>
</li>
<?php 
/*
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <?= __('Ancient') ?>
    </a>
    <div class="dropdown-menu" aria-labelledby="langDropdown">
        <?= $this->Html->link('Score', ['controller' => 'Collectedchests', 'action' => 'score'], ['class' => 'dropdown-item']) ?>
        <?= $this->Html->link('Goals', ['controller' => 'StandardChests', 'action' => 'weights'], ['class' => 'dropdown-item']) ?>
        <?= $this->Html->link('History', '/pages/underconstruction', ['class' => 'dropdown-item']) ?>
        
    </div>
</li>
*/
?>

<li class="nav-item d-none d-sm-inline-block">
  <?= $this->Html->link(__('Bank'), '/pages/underconstruction', ['class' => 'nav-link']) ?>
</li>

