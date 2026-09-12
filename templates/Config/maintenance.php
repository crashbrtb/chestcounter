<?php
/**
 * Is the daily maintenance in the server's crontab, and put it there.
 *
 * All times are picked and displayed in UTC.
 *
 * @var \App\View\AppView $this
 * @var array<string, mixed> $status
 * @var array<string> $times
 * @var array<int, array<string>> $suggestions
 * @var array<int> $runChoices
 * @var array<string> $defaultTimes
 * @var string $scheduleZone
 * @var string $manualBlock
 * @var array<string, mixed> $backup
 * @var \App\Service\DatabaseBackupService $backupService
 * @var string $backupLocalTime
 * @var string $backupDefaultDir
 * @var int $backupMinDays
 * @var int $backupMaxDays
 */

use App\Service\MaintenanceScheduleService;

$this->assign('title', __('Maintenance'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('Config'), 'url' => ['action' => 'index']],
    ['title' => __('Maintenance')],
]);

$this->Html->css('branding', ['block' => 'css']);

$maxRuns = MaintenanceScheduleService::MAX_RUNS;
$chosenCount = count($times);
$serverOffset = $status['serverOffsetMinutes'];
?>
<div class="content-page-wrap maintenance-page">

    <div class="score-toolbar">
        <div class="score-title-group">
            <h1 class="score-title">
                <i class="fas fa-clock text-primary"></i> <?= __('Maintenance') ?>
            </h1>
            <p class="cycle-subtitle">
                <?= __('The scheduled tasks: archiving finished cycles, recording finished events, purging old '
                    . 'chests and backing up the database') ?>
            </p>
        </div>
        <div class="toolbar-actions">
            <?= $this->Html->link(
                '<i class="fas fa-sync-alt mr-1"></i>' . __('Check again'),
                ['action' => 'maintenance'],
                ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>

    <!-- What the crontab says right now -->
    <div class="brand-section">
        <div class="brand-section-head">
            <h2><i class="fas fa-stethoscope text-primary"></i> <?= __('Current status') ?></h2>
            <p class="section-hint">
                <?= __('Read from the crontab of the user the web server runs as, every time this page is opened.') ?>
            </p>
        </div>

        <?php if ($status['unavailable'] !== null): ?>
            <div class="cron-verdict is-unknown">
                <i class="fas fa-question-circle"></i>
                <div>
                    <strong><?= __('The crontab cannot be checked from here') ?></strong>
                    <p><?= h($status['unavailable']) ?></p>
                    <p class="mb-0"><?= __('The entry can still be installed by hand: copy the block at the bottom of this page.') ?></p>
                </div>
            </div>
        <?php elseif ($status['error'] !== null): ?>
            <div class="cron-verdict is-unknown">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong><?= __('The crontab could not be read') ?></strong>
                    <p class="mb-0"><?= h($status['error']) ?></p>
                </div>
            </div>
        <?php elseif ($status['installed']): ?>
            <div class="cron-verdict is-ok">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong><?= __('Scheduled') ?></strong>
                    <p class="mb-0">
                        <?= __(
                            'The maintenance is in the crontab and runs {0} time(s) a day.',
                            count($status['maintenanceEntries'])
                        ) ?>
                        <?php if ($status['backupInstalled']): ?>
                            <?= __('The database backup is in there too.') ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="cron-verdict is-missing">
                <i class="fas fa-times-circle"></i>
                <div>
                    <strong><?= __('Not scheduled') ?></strong>
                    <p class="mb-0">
                        <?= __('Nothing is running the maintenance. Finished cycles are not being archived and '
                            . 'old chests are not being purged. Install it below.') ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($status['installedTimes'] !== []): ?>
            <div class="table-responsive cron-times">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th><?= __('Scheduled times (UTC)') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($status['installedTimes'] as $time): ?>
                            <tr>
                                <td><strong><?= h($time) ?></strong> UTC</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($status['entries'] !== []): ?>
            <p class="cron-label"><?= __('The lines in the crontab:') ?></p>
            <pre class="cron-code"><?= h(implode("\n", $status['entries'])) ?></pre>
        <?php endif; ?>

        <ul class="cron-facts">
            <li>
                <i class="fas fa-hourglass-half"></i>
                <?php if ($status['lastRun'] !== null): ?>
                    <?= __('Last cron output written {0}.', $this->Time->nice($status['lastRun'])) ?>
                <?php else: ?>
                    <?= __('The cron log has never been written, so the task has probably never run here.') ?>
                <?php endif; ?>
            </li>
            <li>
                <i class="fas fa-globe"></i>
                <?= __('All maintenance schedules and tasks follow UTC (Coordinated Universal Time).') ?>
            </li>
            <?php if ($serverOffset !== null): ?>
                <li>
                    <i class="fas fa-server"></i>
                    <?= __('The server clock is {0}.', sprintf(
                        'UTC%+03d:%02d',
                        intdiv((int)$serverOffset, 60),
                        abs((int)$serverOffset % 60)
                    )) ?>
                    <?php if ((int)$serverOffset !== 0): ?>
                        <small class="text-muted">
                            <?= __('The entries declare CRON_TZ=UTC, so this does not change when they run.') ?>
                        </small>
                    <?php endif; ?>
                </li>
            <?php endif; ?>
        </ul>

        <?php if ($status['unmanaged'] !== []): ?>
            <div class="alert alert-warning mb-0">
                <strong><i class="fas fa-exclamation-triangle mr-1"></i><?= __('Entries added by hand') ?></strong>
                <p class="mb-2">
                    <?= __('These call the maintenance but were not installed from this page, so saving here will not '
                        . 'change or remove them. Delete them with <code>crontab -e</code> if you do not want the task '
                        . 'running twice.') ?>
                </p>
                <pre class="cron-code mb-0"><?= h(implode("\n", $status['unmanaged'])) ?></pre>
            </div>
        <?php endif; ?>
    </div>

    <!-- Choosing the schedule -->
    <?= $this->Form->create(null, ['id' => 'cron-form']) ?>
    <div class="brand-section">
        <div class="brand-section-head">
            <h2><i class="fas fa-calendar-check text-primary"></i> <?= __('Schedule') ?></h2>
            <p class="section-hint">
                <?= __('Pick the times in UTC. The schedule holds whatever timezone the server keeps.') ?>
            </p>
        </div>

        <div class="form-group cron-runs">
            <?= $this->Form->label('runs', __('How many times a day')) ?>
            <select name="runs" id="cron-runs" class="form-control">
                <?php foreach ($runChoices as $choice): ?>
                    <option value="<?= (int)$choice ?>" <?= $choice === $chosenCount ? 'selected' : '' ?>>
                        <?= $choice === count($defaultTimes)
                            ? __('{0} times a day (suggested)', $choice)
                            : ($choice === 1 ? __('Once a day') : __('{0} times a day', $choice)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">
                <?= __(
                    'Twice a day is the suggestion: {0} and {1} UTC, quiet hours spaced across the day. '
                        . 'Changing this fills in suggested times, which you can then edit.',
                    $defaultTimes[1] ?? '17:15',
                    $defaultTimes[0] ?? '05:15'
                ) ?>
            </small>
        </div>

        <div class="cron-time-grid">
            <?php for ($i = 0; $i < $maxRuns; $i++): ?>
                <?php $visible = $i < $chosenCount; ?>
                <div class="cron-time-row<?= $visible ? '' : ' is-hidden' ?>" data-index="<?= $i ?>">
                    <label for="cron-time-<?= $i ?>"><?= __('Run {0} (UTC)', $i + 1) ?></label>
                    <input type="time" id="cron-time-<?= $i ?>" name="times[]" class="form-control cron-time"
                        value="<?= h($times[$i] ?? '') ?>" <?= $visible ? 'required' : 'disabled' ?>>
                </div>
            <?php endfor; ?>
        </div>

        <div class="event-form-actions">
            <?= $this->Form->button(
                '<i class="fas fa-save mr-1"></i>' . ($status['installed'] ? __('Save schedule') : __('Install in crontab')),
                ['class' => 'btn btn-primary', 'escapeTitle' => false]
            ) ?>
            <?php if ($status['installed']): ?>
                <?= $this->Form->button(
                    '<i class="fas fa-trash mr-1"></i>' . __('Remove from crontab'),
                    [
                        'class' => 'btn btn-outline-danger ml-2',
                        'escapeTitle' => false,
                        'name' => 'remove',
                        'value' => '1',
                        'formnovalidate' => true,
                        'confirm' => __('Remove the maintenance from the crontab? Nothing will archive cycles, purge '
                            . 'old chests or back up the database until it is installed again.'),
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    </div>
    <?= $this->Form->end() ?>

    <!-- The nightly database backup -->
    <?= $this->Form->create(null, ['id' => 'backup-form']) ?>
    <?= $this->Form->hidden('section', ['value' => 'backup']) ?>
    <div class="brand-section">
        <div class="brand-section-head">
            <h2><i class="fas fa-database text-primary"></i> <?= __('Database backup') ?></h2>
            <p class="section-hint">
                <?= __(
                    'A gzipped mysqldump of the whole database, taken every day at {0} UTC — the game reset — so '
                        . 'each dump holds one cycle day exactly as the game closed it. The time is fixed for that '
                        . 'reason; the folder and how long dumps are kept are yours to choose.',
                    h($backup['utcTime'])
                ) ?>
            </p>
        </div>

        <?php if (!$backup['enabled']): ?>
            <div class="cron-verdict is-missing">
                <i class="fas fa-times-circle"></i>
                <div>
                    <strong><?= __('Not backing up') ?></strong>
                    <p class="mb-0">
                        <?= __('Nothing is dumping the database. Turn it on below.') ?>
                    </p>
                </div>
            </div>
        <?php elseif (!$status['backupInstalled'] && $status['unavailable'] === null): ?>
            <div class="cron-verdict is-unknown">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong><?= __('Turned on, but not in the crontab') ?></strong>
                    <p class="mb-0">
                        <?= __('The backup is turned on but nothing is calling it. Save the schedule above, or '
                            . 'install the block by hand.') ?>
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="cron-verdict is-ok">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong><?= __('Backing up daily') ?></strong>
                    <p class="mb-0">
                        <?= __(
                            'Every day at {0} UTC, keeping {1} day(s) of dumps.',
                            h($backup['utcTime']),
                            (int)$backup['retentionDays']
                        ) ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <ul class="cron-facts">
            <li>
                <i class="fas fa-folder-open"></i>
                <?= __('Saving to') ?>
                <code><?= h($backup['resolved']) ?></code>
                <?php if ($backup['directory'] !== $backup['resolved']): ?>
                    <small class="text-muted"><?= __('(configured as {0})', h($backup['directory'])) ?></small>
                <?php endif; ?>
                <?php if (!$backup['exists']): ?>
                    <small class="text-muted"><?= __('— created on the first run.') ?></small>
                <?php elseif (!$backup['writable']): ?>
                    <small class="text-danger"><?= __('— this folder cannot be written to.') ?></small>
                <?php endif; ?>
            </li>
            <li>
                <i class="fas fa-archive"></i>
                <?php if ($backup['count'] > 0): ?>
                    <?= __(
                        '{0} dump(s) there, {1} in all. Newest: {2}.',
                        (int)$backup['count'],
                        h($backupService->humanBytes((int)$backup['bytes'])),
                        $backup['latest'] !== null ? h($this->Time->nice($backup['latest'])) : '—'
                    ) ?>
                <?php elseif ($backup['exists']): ?>
                    <?= __('No dumps in that folder yet.') ?>
                <?php else: ?>
                    <?= __('The folder is not there yet, so there are no dumps.') ?>
                <?php endif; ?>
            </li>
            <li>
                <i class="fas fa-file-alt"></i>
                <?= __('Every step is appended to {0}.', '<code>' . h($backup['logPath']) . '</code>') ?>
                <?php if ($status['backupLastRun'] !== null): ?>
                    <small class="text-muted">
                        <?= __('Cron last wrote its own log {0}.', $this->Time->nice($status['backupLastRun'])) ?>
                    </small>
                <?php endif; ?>
            </li>
            <li>
                <i class="fas fa-terminal"></i>
                <?php if (!$backup['isMysql']): ?>
                    <span class="text-danger">
                        <?= __('This installation is not on MySQL or MariaDB, so mysqldump cannot back it up.') ?>
                    </span>
                <?php elseif ($backup['shellReason'] !== null): ?>
                    <span class="text-danger"><?= h($backup['shellReason']) ?></span>
                    <small class="text-muted">
                        <?= __('The command may still work from a real shell, where cron runs it.') ?>
                    </small>
                <?php elseif ($backup['dumpBinary'] !== null): ?>
                    <?= __('mysqldump found at {0}.', '<code>' . h($backup['dumpBinary']) . '</code>') ?>
                <?php else: ?>
                    <span class="text-danger">
                        <?= __('mysqldump was not found on this server. Install the MySQL/MariaDB client tools, '
                            . 'or no backup can be taken.') ?>
                    </span>
                <?php endif; ?>
            </li>
        </ul>

        <div class="form-group">
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="backup-enabled" name="enabled" value="1"
                    <?= $backup['enabled'] ? 'checked' : '' ?>>
                <label class="custom-control-label" for="backup-enabled">
                    <?= __('Back the database up every day') ?>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label for="backup-directory"><?= __('Folder to save to') ?></label>
            <input type="text" class="form-control" id="backup-directory" name="directory" maxlength="255"
                value="<?= h($backup['directory']) ?>" placeholder="<?= h($backupDefaultDir) ?>">
            <small class="form-text text-muted">
                <?= __(
                    'A full path, because cron does not start in the site directory. {0} is the home directory of the user the site runs as, so the default {1} needs nothing set up. The folder is created if it is not there.',
                    '<code>~</code>',
                    '<code>' . h($backupDefaultDir) . '</code>'
                ) ?>
            </small>
        </div>

        <div class="form-group cron-runs">
            <label for="backup-retention"><?= __('Keep dumps for') ?></label>
            <div class="input-group">
                <input type="number" class="form-control" id="backup-retention" name="retention_days"
                    min="<?= (int)$backupMinDays ?>" max="<?= (int)$backupMaxDays ?>" step="1"
                    value="<?= (int)$backup['retentionDays'] ?>">
                <div class="input-group-append">
                    <span class="input-group-text"><?= __('days') ?></span>
                </div>
            </div>
            <small class="form-text text-muted">
                <?= __(
                    'Anything older is deleted after each backup, so the folder holds this many days and no more. '
                        . 'Between {0} and {1} days; the old script kept {2}.',
                    (int)$backupMinDays,
                    (int)$backupMaxDays,
                    7
                ) ?>
            </small>
        </div>

        <div class="event-form-actions">
            <?= $this->Form->button(
                '<i class="fas fa-save mr-1"></i>' . __('Save backup settings'),
                ['class' => 'btn btn-primary', 'escapeTitle' => false]
            ) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>

    <!-- The same thing, for hands -->
    <div class="brand-section">
        <div class="brand-section-head">
            <h2><i class="fas fa-terminal text-primary"></i> <?= __('Install it by hand') ?></h2>
            <p class="section-hint">
                <?= __('For the times shown in the form above. Run <code>crontab -e</code> on the server and paste '
                    . 'this at the end of the file.') ?>
            </p>
        </div>

        <pre class="cron-code" id="cron-manual"><?= h($manualBlock) ?></pre>

        <button type="button" class="btn btn-outline-secondary btn-sm" id="cron-copy">
            <i class="fas fa-copy mr-1"></i><?= __('Copy') ?>
        </button>
        <small class="form-text text-muted">
            <?= __('This block is what the buttons above write. Saving the schedule here rewrites everything '
                . 'between the two marker comments and leaves the rest of the crontab alone.') ?>
        </small>
    </div>

    <div class="alert alert-info brand-footnote">
        <i class="fas fa-info-circle mr-1"></i>
        <?= __(
            'You can also run the task yourself at any time: {0} from the application directory, or add {1} to see what it would do without touching the database.',
            '<code>php bin/cake.php daily_maintenance</code>',
            '<code>--dry-run</code>'
        ) ?>
        <br>
        <?= __(
            'The backup is the same: {0} takes one now, and {1} takes one even while the nightly backup is turned off.',
            '<code>php bin/cake.php database_backup</code>',
            '<code>--force</code>'
        ) ?>
    </div>
</div>

<?php $this->start('script'); ?>
<script>
    (function () {
        var SUGGESTIONS = <?= json_encode($suggestions) ?>;

        var runs = document.getElementById('cron-runs');
        var rows = Array.prototype.slice.call(document.querySelectorAll('.cron-time-row'));

        function show(count) {
            var suggestion = SUGGESTIONS[count] || [];
            rows.forEach(function (row, index) {
                var input = row.querySelector('.cron-time');
                var visible = index < count;
                row.classList.toggle('is-hidden', !visible);
                input.disabled = !visible;
                input.required = visible;
                if (visible && suggestion[index]) {
                    input.value = suggestion[index];
                }
            });
        }

        if (runs) {
            runs.addEventListener('change', function () {
                show(parseInt(runs.value, 10));
            });
        }

        var copy = document.getElementById('cron-copy');
        if (copy) {
            copy.addEventListener('click', function () {
                var text = document.getElementById('cron-manual').textContent;
                var done = function () {
                    copy.innerHTML = '<i class="fas fa-check mr-1"></i><?= __('Copied') ?>';
                    setTimeout(function () {
                        copy.innerHTML = '<i class="fas fa-copy mr-1"></i><?= __('Copy') ?>';
                    }, 1800);
                };

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(done);
                    return;
                }
                // Still served over plain HTTP on some installs, where the
                // clipboard API is not available.
                var area = document.createElement('textarea');
                area.value = text;
                document.body.appendChild(area);
                area.select();
                document.execCommand('copy');
                document.body.removeChild(area);
                done();
            });
        }
    })();
</script>
<?php $this->end(); ?>
