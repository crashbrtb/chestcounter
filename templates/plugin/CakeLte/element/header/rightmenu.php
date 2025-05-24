<li class="nav-item d-none d-sm-inline-block">
  <?= $this->Html->link(__('Login'), '/login', ['class' => 'nav-link']) ?>
</li>

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <?= __('Language') ?>
    </a>
    <div class="dropdown-menu" aria-labelledby="langDropdown">
        <?= $this->Html->link('English', ['controller' => 'App', 'action' => 'changeLanguage', 'en_US'], ['class' => 'dropdown-item']) ?>
        <?= $this->Html->link('Português', ['controller' => 'App', 'action' => 'changeLanguage', 'pt_BR'], ['class' => 'dropdown-item']) ?>
        
    </div>
</li>

  