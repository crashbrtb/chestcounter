<?php
$identity = $this->request->getAttribute('identity');
$isLoggedIn = $identity !== null;
?>

<?php if (!$isLoggedIn): ?>
    <li class="nav-item d-none d-sm-inline-block">
        <?= $this->Html->link(
            '<i class="fas fa-sign-in-alt nav-icon"></i> ' . __('Login'),
            ['controller' => 'Users', 'action' => 'login'],
            ['class' => 'nav-link', 'escape' => false]
        ) ?>
    </li>
<?php else: ?>
    <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
            <i class="fas fa-user-circle nav-icon"></i> <!-- Ícone de usuário genérico -->
            <span class="d-none d-md-inline"><?= h($identity->get('username')) // Ou outro campo como 'email' ou 'nome_completo' ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <!-- User image -->
            <li class="user-header bg-primary">
                <!-- Você pode adicionar uma imagem do usuário aqui se tiver -->
                <p>
                    <?= h($identity->get('username')) ?>
                    <small><?= __('Member since {0}', $identity->get('created')->nice()) // Exemplo de data de criação ?></small>
                </p>
            </li>
            <!-- Menu Footer-->
            <li class="user-footer">
                <?php // $this->Html->link(__('Profile'), ['controller' => 'Users', 'action' => 'profile', $identity->getIdentifier()], ['class' => 'btn btn-default btn-flat']) ?>
                <?= $this->Html->link(
                    __('Logout'),
                    ['controller' => 'Users', 'action' => 'logout'],
                    ['class' => 'btn btn-default btn-flat float-right']
                ) ?>
            </li>
        </ul>
    </li>
<?php endif; ?>

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-language nav-icon"></i> <span class="d-none d-md-inline"></span>
    </a>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="langDropdown">
        <?= $this->Html->link('English', ['controller' => 'App', 'action' => 'changeLanguage', 'en_US'], ['class' => 'dropdown-item']) ?>
        <?= $this->Html->link('Português', ['controller' => 'App', 'action' => 'changeLanguage', 'pt_BR'], ['class' => 'dropdown-item']) ?>
    </div>
</li>

  