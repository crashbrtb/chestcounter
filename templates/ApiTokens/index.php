<?php
/**
 * Personal tokens for the EventUploader.
 *
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\ApiToken> $tokens
 * @var array{name: string, token: string}|null $issued
 * @var string $siteUrl
 */

$this->assign('title', __('API Tokens'));
?>
<div class="content-page-wrap">

    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title"><i class="fas fa-key text-primary"></i> <?= __('API Tokens') ?></h1>
            <p class="cycle-subtitle"><?= __('Tokens the EventUploader uses to send tournament rankings to this site') ?></p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-list mr-1"></i>' . __('Manage Events'),
                ['controller' => 'Events', 'action' => 'manage'],
                ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <?php if (!empty($issued)): ?>
        <div class="alert alert-success">
            <h5><i class="fas fa-check-circle mr-1"></i> <?= __('Token "{0}" created', h($issued['name'])) ?></h5>
            <p class="mb-2">
                <?= __('Copy it now and paste it in the EventUploader, together with the site address. It will not be shown again: if it is lost, revoke it and create another.') ?>
            </p>
            <div class="input-group mb-2">
                <input type="text" class="form-control" id="issuedToken" value="<?= h($issued['token']) ?>" readonly onclick="this.select()">
                <div class="input-group-append">
                    <button type="button" class="btn btn-primary" onclick="copyToken()">
                        <i class="fas fa-copy mr-1"></i><?= __('Copy') ?>
                    </button>
                </div>
            </div>
            <small><?= __('Site address: {0}', h($siteUrl)) ?></small>
        </div>
    <?php endif; ?>

    <div class="event-form-card">
        <h2><i class="fas fa-plus text-primary"></i> <?= __('New token') ?></h2>
        <p class="section-hint">
            <?= __('Create one per computer the EventUploader runs on. The token belongs to you and can only send rankings; it cannot read or change anything else.') ?>
        </p>
        <?= $this->Form->create(null, ['url' => ['action' => 'add'], 'class' => 'd-flex flex-wrap', 'style' => 'gap: 10px;']) ?>
        <input type="text" name="name" class="form-control" style="max-width: 320px;" maxlength="60" required
               placeholder="<?= h(__('e.g. Clan leader PC')) ?>">
        <?= $this->Form->button('<i class="fas fa-key mr-1"></i>' . __('Create token'), ['class' => 'btn btn-primary', 'escapeTitle' => false]) ?>
        <?= $this->Form->end() ?>
    </div>

    <div class="event-standings-card">
        <div class="event-standings-header">
            <h2 class="event-standings-title"><i class="fas fa-list text-primary"></i> <?= __('Tokens') ?></h2>
        </div>
        <?php if (count($tokens) > 0): ?>
            <div class="event-table-wrap">
                <table class="event-table">
                    <thead>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Owner') ?></th>
                            <th><?= __('Starts with') ?></th>
                            <th><?= __('Created (UTC)') ?></th>
                            <th><?= __('Last used (UTC)') ?></th>
                            <th><?= __('State') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tokens as $token): ?>
                            <tr>
                                <td><?= h($token->name) ?></td>
                                <td><?= h($token->user->name ?? '-') ?></td>
                                <td><code><?= h($token->prefix) ?>…</code></td>
                                <td><?= h($token->created?->format('d/m/Y H:i')) ?></td>
                                <td>
                                    <?= $token->last_used_at ? h($token->last_used_at->format('d/m/Y H:i')) : __('never') ?>
                                    <?php if ($token->last_used_ip): ?>
                                        <small class="d-block text-muted"><?= h($token->last_used_ip) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($token->is_active): ?>
                                        <span class="event-state state-running"><?= __('Active') ?></span>
                                    <?php else: ?>
                                        <span class="event-state state-cancelled"><?= $token->revoked_at ? __('Revoked') : __('Expired') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($token->is_active): ?>
                                        <?= $this->Form->postLink(
                                            '<i class="fas fa-ban"></i>',
                                            ['action' => 'revoke', $token->id],
                                            [
                                                'class' => 'btn btn-danger btn-xs',
                                                'escape' => false,
                                                'title' => __('Revoke'),
                                                'confirm' => __('Revoke the token "{0}"? The EventUploader using it stops working at once.', $token->name),
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="event-empty">
                <i class="fas fa-key"></i>
                <p><?= __('No token has been created yet.') ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->start('script'); ?>
<script>
    function copyToken() {
        var input = document.getElementById('issuedToken');
        input.select();
        if (navigator.clipboard) {
            navigator.clipboard.writeText(input.value);
        } else {
            document.execCommand('copy');
        }
    }
</script>
<?php $this->end(); ?>
