<?php
?>
<style>
    table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        margin-bottom: 1em;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    th, td {
        border: none;
        padding: 12px 16px;
        text-align: center;
    }
    th {
        background:rgb(171, 192, 247);
        color:rgb(21, 39, 67);
        font-weight: 600;
        font-size: 1.05em;
    }
    tr:nth-child(even) {
        background: #f8fafc;
    }
    tr:nth-child(odd) {
        background: #e9f1fb;
    }
    tr:hover {
        background: #c7d7f5;
        transition: background 0.2s;
    }
    table thead tr:first-child th:first-child {
        border-top-left-radius: 12px;
    }
    table thead tr:first-child th:last-child {
        border-top-right-radius: 12px;
    }
    table tbody tr:last-child td:first-child {
        border-bottom-left-radius: 12px;
    }
    table tbody tr:last-child td:last-child {
        border-bottom-right-radius: 12px;
    }
    .filter-form-container {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 10px;
        margin-top: -40px;
    }
</style>

<h1><?= __('Players Score') ?></h1>

<div class="filter-form-container">
    <?= $this->Form->create(null, ['type' => 'get', 'style' => 'display: flex; gap: 8px; align-items: center;']) ?>
        <?= $this->Form->select('cycle', $cycleOptions, ['default' => $selectedCycleOffset]) ?>
        <?= $this->Form->button(__('Filter')) ?>
    <?= $this->Form->end() ?>
</div>

<h5><?= $cycleOptions[$selectedCycleOffset] ?></h5>
<br>

<?php if (!empty($playerChestCounts)): ?>
    <table>
        <thead>
            <tr>
                <th><?= __('Player') ?></th>
                <th><?= __('Total Chests') ?></th>
                <th><?= __('Final Score') ?></th>
                <?php
                $allSources = [];
                foreach ($playerChestCounts as $counts) {
                    $allSources = array_unique(array_merge($allSources, array_keys($counts)));
                }
                sort($allSources);
                foreach ($allSources as $source): ?>
                    <th><?= h($source) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($playerChestCounts as $player => $counts): ?>
                <tr>
                    <td><?= h($player) ?></td>
                    <td><?= isset($playerTotalChests[$player]) ? $playerTotalChests[$player] : 0 ?></td>
                    <?php
                    // Determina a cor do texto com base na pontuação usando degradê
                    $score = isset($playerFinalScores[$player]) ? $playerFinalScores[$player] : 0;
                    $percentage = min(max($score / $minimumChestScore, 0), 1); // Calcula a porcentagem entre 0 e 1
                    $red = (1 - $percentage) * 255; // Quanto menor a pontuação, mais vermelho
                    $green = $percentage * 255; // Quanto maior a pontuação, mais verde
                    $color = sprintf('rgb(%d, %d, 0)', $red, $green); // Combina vermelho e verde
                    ?>
                    <td style="color: <?= $color ?>;"><?= $score ?></td>
                    <?php foreach ($allSources as $source): ?>
                        <td><?= isset($counts[$source]) ? $counts[$source] : 0 ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p><?= __('No chests collected in this cycle.') ?></p>
<?php endif; ?>
<br>
<?php
// Calcular o tempo restante para o ciclo atual (se estiver visualizando o ciclo atual)
$now = \Cake\I18n\FrozenTime::now();
$startOfCurrentCycle = \Cake\I18n\FrozenTime::parse($currentCycleFormatted['start'], 'UTC')->setTimezone('America/Sao_Paulo');
$endOfCurrentCycle = \Cake\I18n\FrozenTime::parse($currentCycleFormatted['end'], 'UTC')->setTimezone('America/Sao_Paulo');

if ($now >= $startOfCurrentCycle && $now <= $endOfCurrentCycle) {
    $diff = $now->diff($endOfCurrentCycle);
    $daysRemaining = $diff->days;
    $hoursRemaining = $diff->h;
    $minutesRemaining = $diff->i;
    $secondsRemaining = $diff->s;
    echo "<p>";
    echo __('Time remaining until the end of the Current Cycle: ');
    if ($daysRemaining > 0) {
        echo __("{0} day{1}, ", $daysRemaining, $daysRemaining > 1 ? 's' : '');
    }
    echo __("{0} hour{1}, ", $hoursRemaining, $hoursRemaining > 1 ? 's' : '');
    echo __("{0} minute{1}, ", $minutesRemaining, $minutesRemaining > 1 ? 's' : '');
    echo __("{0} second{1}", $secondsRemaining, $secondsRemaining > 1 ? 's' : '');
    echo "</p>";
} else {
    echo "<p>" . __('The Current Cycle has already ended.') . "</p>";
}
?>