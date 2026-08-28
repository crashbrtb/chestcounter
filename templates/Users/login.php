<?php
/**
 * @var \App\View\AppView $this
 */
use Cake\Core\Configure;

$this->layout = 'CakeLte/layout/login';
$googleClientId = Configure::read('Google.clientId');
?>
<script src="https://accounts.google.com/gsi/client?hl=en" async defer></script>

<div class="card">
    <div class="card-body login-card-body">
        <p class="login-box-msg"><?= __('Sign in to start your session') ?></p>

        <?= $this->Form->create() ?>

        <?= $this->Form->control('email', [
            'label' => false,
            'placeholder' => __('Email'),
            'append' => '<i class="fas fa-user"></i>',
        ]) ?>

        <?= $this->Form->control('password', [
            'label' => false,
            'placeholder' => __('Password'),
            'append' => '<i class="fas fa-lock"></i>',
        ]) ?>

        <div class="row">
            <div class="col-8">
                <?= $this->Form->control('remember_me', ['type' => 'checkbox', 'custom' => true]) ?>
            </div>
            <div class="col-4">
                <?= $this->Form->control(__('Sign In'), ['type' => 'submit', 'class' => 'btn btn-primary btn-block']) ?>
            </div>
        </div>

        <?= $this->Form->end() ?>

        <?php if (!empty($googleClientId) && $googleClientId !== 'YOUR_GOOGLE_CLIENT_ID_HERE'): ?>
        <!-- Google Sign-In -->
        <div class="social-auth-links text-center mb-3 mt-3">
            <p class="text-muted">— <?= __('OR') ?> —</p>
            <div id="g_id_onload"
                 data-client_id="<?= h($googleClientId) ?>"
                 data-login_uri="<?= $this->Url->build(['controller' => 'Users', 'action' => 'googleLogin'], ['fullBase' => true]) ?>"
                 data-auto_prompt="false"
                 data-context="signin"
                 data-ux_mode="redirect">
            </div>
            <div style="display: flex; justify-content: center; min-height: 44px;">
                <div id="googleBtn" class="g_id_signin"
                     data-type="standard"
                     data-size="large"
                     data-theme="outline"
                     data-text="signin_with"
                     data-shape="rectangular"
                     data-logo_alignment="left"
                     data-width="280">
                </div>
            </div>
        </div>
        <script>
            function initGoogleBtn() {
                if (typeof google !== 'undefined' && google.accounts && google.accounts.id) {
                    google.accounts.id.initialize({
                        client_id: "<?= h($googleClientId) ?>",
                        login_uri: "<?= $this->Url->build(['controller' => 'Users', 'action' => 'googleLogin'], ['fullBase' => true]) ?>",
                        ux_mode: "redirect",
                        auto_prompt: false
                    });
                    const btn = document.getElementById("googleBtn");
                    if (btn) {
                        google.accounts.id.renderButton(btn, {
                            type: "standard",
                            theme: "outline",
                            size: "large",
                            text: "signin_with",
                            shape: "rectangular",
                            logo_alignment: "left",
                            width: 280
                        });
                    }
                }
            }
            window.addEventListener('load', initGoogleBtn);
            if (document.readyState === 'complete') {
                initGoogleBtn();
            }
        </script>
        <?php endif; ?>

        <!-- /.social-auth-links -->
        <?php
        /*
        <p class="mb-1">
            <?= $this->Html->link(__('I forgot my password'), ['action' => 'recovery']) ?>
        </p>
        <p class="mb-0">
            <?= $this->Html->link(__('Register a new membership'), ['action' => 'register']) ?>
        </p>
        */
        ?>
    </div>
    <!-- /.login-card-body -->
</div>