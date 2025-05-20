<h1>Pontuação dos Jogadores</h1>

<?= $this->Form->create(null, ['type' => 'get']) ?>
    <?= $this->Form->label('cycle', 'Selecionar Ciclo:') ?>
    <?= $this->Form->select('cycle', $cycleOptions, ['default' => $selectedCycleOffset]) ?>
    <?= $this->Form->button('Filtrar') ?>
<?= $this->Form->end() ?>

<h2>Ciclo Selecionado: <?= $cycleOptions[$selectedCycleOffset] ?></h2>

<?php if (!empty($playerChestCounts)): ?>
    <table>
        <thead>
            <tr>
                <th>Jogador</th>
                <th>Total Baús</th>
                <th>Pontuação Final</th>
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
    <p>Nenhum baú coletado neste ciclo.</p>
<?php endif; ?>

<?php
// Calcular o tempo restante para o ciclo atual (se estiver visualizando o ciclo atual)
$now = \Cake\I18n\FrozenTime::now('America/Sao_Paulo');
$startOfCurrentCycle = \Cake\I18n\FrozenTime::parse($currentCycleFormatted['start'], 'UTC')->setTimezone('America/Sao_Paulo');
$endOfCurrentCycle = \Cake\I18n\FrozenTime::parse($currentCycleFormatted['end'], 'UTC')->setTimezone('America/Sao_Paulo');

if ($now >= $startOfCurrentCycle && $now <= $endOfCurrentCycle) {
    $diff = $now->diff($endOfCurrentCycle);
    $daysRemaining = $diff->days;
    $hoursRemaining = $diff->h;
    $minutesRemaining = $diff->i;
    $secondsRemaining = $diff->s;
    echo "<p>Tempo restante para o fim do Ciclo Atual: ";
    if ($daysRemaining > 0) {
        echo "{$daysRemaining} dia" . ($daysRemaining > 1 ? 's' : '') . ", ";
    }
    echo "{$hoursRemaining} hora" . ($hoursRemaining > 1 ? 's' : '') . ", ";
    echo "{$minutesRemaining} minuto" . ($minutesRemaining > 1 ? 's' : '') . ", ";
    echo "{$secondsRemaining} segundo" . ($secondsRemaining > 1 ? 's' : '') . "</p>";
} else {
    echo "<p>O Ciclo Atual já terminou ou ainda não começou.</p>";
}
?>