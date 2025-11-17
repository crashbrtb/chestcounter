<?php
use Cake\ORM\TableRegistry;

$identity = $this->request->getAttribute('identity');
$isLoggedIn = $identity !== null;
$isAdmin = false; // Inicia como falso
if ($isLoggedIn) {
    // Assumindo que a propriedade na entidade User que contém os roles associados é 'roles'
    // e que a entidade Role tem uma propriedade 'name' para o nome do role.
    $userAssociatedRoles = $identity->get('roles'); 
    if (!empty($userAssociatedRoles) && (is_array($userAssociatedRoles) || $userAssociatedRoles instanceof \Traversable)) {
        foreach ($userAssociatedRoles as $roleEntity) {
            if (isset($roleEntity->name) && $roleEntity->name === 'admin') {
                $isAdmin = true;
                break; 
            }
        }
    }
}

// Verificar se a função do banco está habilitada
$configTable = TableRegistry::getTableLocator()->get('Config');
$bankFunctionConfig = $configTable->find()
    ->where(['param' => 'bank_function'])
    ->first();
$bankFunctionEnabled = $bankFunctionConfig && (int)$bankFunctionConfig->value === 1;

if ($isAdmin):
?>
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
        <?= __('Admin') ?>
    </a>
    <ul class="dropdown-menu" aria-labelledby="langDropdown">
        <li class="dropdown-submenu dropdown-hover">
            <a id="chestsDropdownMenuLink" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle">
                <?= __('Members') ?>
            </a>
            <ul aria-labelledby="chestsDropdownMenuLink" class="dropdown-menu border-0 shadow">
                <li>
                    <?= $this->Html->link(__('List'), ['controller' => 'Members', 'action' => 'index'], ['class' => 'dropdown-item']) ?>
                </li>
                <li>
                    <?= $this->Html->link(__('Update'), ['controller' => 'Members', 'action' => 'updateFromCollectedChests'], ['class' => 'dropdown-item']) ?>
                </li>
                <li>
                    <?= $this->Html->link(__('Names Mapping'), ['controller' => 'PlayerNameMappings', 'action' => 'index'], ['class' => 'dropdown-item']) ?>
                </li>
            </ul>
        </li>
        <?= $this->Html->link('Users', ['controller' => 'Users', 'action' => 'index'], ['class' => 'dropdown-item']) ?>
        <?= $this->Html->link('Roles', ['controller' => 'Roles', 'action' => 'index'], ['class' => 'dropdown-item']) ?>
        <li class="dropdown-submenu dropdown-hover">
            <a id="chestsDropdownMenuLink" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle">
                <?= __('Chests') ?>
            </a>
            <ul aria-labelledby="chestsDropdownMenuLink" class="dropdown-menu border-0 shadow">
                <li>
                    <?= $this->Html->link(__('Standard Chests'), ['controller' => 'StandardChests', 'action' => 'index'], ['class' => 'dropdown-item']) ?>
                </li>
                <li>
                    <?= $this->Html->link(__('Merge Players'), ['controller' => 'CollectedChests', 'action' => 'mergePlayers'], ['class' => 'dropdown-item']) ?>
                </li>
                <li>
                    <?= $this->Html->link(__('Summary last cycles'), ['controller' => 'PlayerCycleSummaries', 'action' => 'index'], ['class' => 'dropdown-item']) ?>
                </li>
            </ul>
        </li>
        <?= $this->Html->link('Configs', ['controller' => 'Config', 'action' => 'index'], ['class' => 'dropdown-item']) ?>
        <?php if ($bankFunctionEnabled): ?>
        <li class="dropdown-submenu dropdown-hover">
            <a id="chestsDropdownMenuLink" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle">
                <?= __('Bank') ?>
            </a>
            <ul aria-labelledby="chestsDropdownMenuLink" class="dropdown-menu border-0 shadow">
                <li>
                    <?= $this->Html->link('Bank Approvals', ['controller' => 'Bank', 'action' => 'approvals'], ['class' => 'dropdown-item']) ?>
                </li>
            </ul>
        </li>
        <?php endif; ?>
        

    </ul>
</li>
<?php
endif;
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
    <?php
    $pendingCount = $pendingApprovalsCount ?? 0;
    $hasPendingApprovals = $pendingCount > 0;
    $iconClass = 'fas fa-user-circle nav-icon' . ($hasPendingApprovals ? ' pending-approvals-icon' : '');
    ?>
    <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
            <i class="<?= $iconClass ?>"></i>
            <span class="d-none d-md-inline"><?= h($identity->get('username')) ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <!-- User image -->
            <li class="user-header bg-primary">
                <p>
                    <?= h($identity->get('username')) ?>
                    <?php if ($hasPendingApprovals): ?>
                        <small class="text-warning" style="font-weight: bold;">
                            <i class="fas fa-exclamation-circle"></i> 
                            <?= __('{0} pending approval(s)', $pendingCount) ?>
                        </small>
                    <?php else: ?>
                        <small><?= __('Member since {0}', $identity->get('created')->nice()) ?></small>
                    <?php endif; ?>
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
    <?php if ($hasPendingApprovals): ?>
    <style>
        .pending-approvals-icon {
            animation: blink 1s infinite;
            color: #ffc107 !important;
        }
        @keyframes blink {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.4;
                transform: scale(1.1);
            }
        }
    </style>
    <?php endif; ?>
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

  