<?php
/**
 * @var \App\View\AppView $this
 * @var array|null $pendingUser
 */

$this->layout = 'CakeLte/layout/login';
$userName = $pendingUser['name'] ?? __('User');
$userEmail = $pendingUser['email'] ?? '';
$userPicture = $pendingUser['picture'] ?? '';
?>

<div class="card card-outline card-warning shadow-lg">
    <div class="card-body login-card-body text-center p-4">
        
        <?php if (!empty($userPicture)): ?>
            <div class="mb-3">
                <img src="<?= h($userPicture) ?>" alt="<?= h($userName) ?>" class="rounded-circle shadow-sm" style="width: 72px; height: 72px; border: 3px solid #ffc107;">
            </div>
        <?php else: ?>
            <div class="mb-3">
                <div class="d-inline-flex align-items-center justify-content-center bg-warning text-white rounded-circle shadow-sm" style="width: 72px; height: 72px;">
                    <i class="fas fa-user-clock fa-2x"></i>
                </div>
            </div>
        <?php endif; ?>

        <h4 class="font-weight-bold text-dark mb-1">
            <?= __('Registration Complete!') ?>
        </h4>
        <p class="text-muted small mb-3">
            <?= __('Awaiting Administrator Approval') ?>
        </p>

        <div class="alert alert-light border text-left p-3 mb-3" style="background-color: #f8f9fa;">
            <div class="d-flex align-items-center mb-2">
                <i class="fab fa-google text-danger mr-2 fa-lg"></i>
                <strong class="text-truncate"><?= h($userName) ?></strong>
            </div>
            <?php if (!empty($userEmail)): ?>
                <div class="text-muted small text-truncate">
                    <i class="fas fa-envelope mr-1"></i> <?= h($userEmail) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-muted text-left mb-4" style="font-size: 0.9rem; line-height: 1.5;">
            <p class="mb-2">
                <i class="fas fa-shield-alt text-warning mr-1"></i>
                <?= __('Your Google account has been successfully registered.') ?>
            </p>
            <p class="mb-0">
                <?= __('For clan security and organization purposes, your access must be <strong>activated by an administrator</strong>. Please contact a clan leader or admin to grant your access.') ?>
            </p>
        </div>

        <div class="pt-2 border-top">
            <a href="<?= $this->Url->build(['action' => 'login']) ?>" class="btn btn-primary btn-block font-weight-bold">
                <i class="fas fa-arrow-left mr-2"></i> <?= __('Back to Login') ?>
            </a>
        </div>

    </div>
</div>
