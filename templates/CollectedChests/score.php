<?php
/**
 * @var \App\View\AppView $this
 * @var array $playerChestCounts
 * @var array $playerTotalChests
 * @var array $playerFinalScores
 * @var array $cycleOptions
 * @var string $selectedCycleOffset
 * @var int $minimumChestScore
 * @var \Cake\I18n\FrozenTime|null $lastUpdate
 * @var array $currentCycleFormatted
 * @var string[] $sourcesWithNonZeroScore // Esta variável virá do controller
 */
?>
<?php
// Define sort variables
$sortColumn = $this->request->getQuery('sort', 'final_score');
$sortDirection = $this->request->getQuery('direction', 'desc');

// Helper to create sort links
$createSortLink = function($column, $title) use ($sortColumn, $sortDirection, $sourcesWithNonZeroScore) {
    $currentParams = $this->request->getQuery();
    $direction = ($sortColumn === $column && $sortDirection === 'asc') ? 'desc' : 'asc';
    $arrow = '';
    if ($sortColumn === $column) {
        $arrow = $sortDirection === 'asc' ? ' ▲' : ' ▼';
    }
    $url = $this->Url->build(['?' => array_merge($currentParams, ['sort' => $column, 'direction' => $direction])]);
    // Adiciona a classe 'active' se esta for a coluna de ordenação atual
    $class = ($sortColumn === $column) ? ' class="active"' : '';
    return '<a href="' . $url . '"' . $class . '>' . h($title) . $arrow . '</a>';
};

// Combine data for sorting
$playersData = [];
if (!empty($playerChestCounts)) {
    // Garante que $sourcesWithNonZeroScore seja um array para evitar erros
    $sourcesWithNonZeroScore = $sourcesWithNonZeroScore ?? [];
    sort($sourcesWithNonZeroScore); // Garante uma ordem consistente para as colunas dinâmicas

    foreach (array_keys($playerChestCounts) as $player) {
        $counts = $playerChestCounts[$player] ?? [];
        $epicCryptScore = 0;
        if (isset($chestScores) && (is_array($chestScores) || $chestScores instanceof \ArrayAccess)) {
            foreach ($counts as $source => $count) {
                if (stripos($source, 'epic') !== false && isset($chestScores[$source])) {
                    $epicCryptScore += $chestScores[$source]->score * $count;
                }
            }
        }

        $playerData = [
            'player' => $player,
            'total_chests' => $playerTotalChests[$player] ?? 0,
            'final_score' => $playerFinalScores[$player] ?? 0,
            'epic_crypt_score' => $epicCryptScore,
            'counts' => $counts,
        ];

        foreach ($sourcesWithNonZeroScore as $source) {
            $playerData[$source] = $counts[$source] ?? 0;
        }
        $playersData[] = $playerData;
    }
}

// Sort the data
if (!empty($playersData)) {
    usort($playersData, function($a, $b) use ($sortColumn, $sortDirection) {
        $valA = $a[$sortColumn] ?? 0;
        $valB = $b[$sortColumn] ?? 0;

        if ($sortColumn === 'player') {
            return $sortDirection === 'asc' ? strcasecmp((string)$valA, (string)$valB) : strcasecmp((string)$valB, (string)$valA);
        }

        if ($valA == $valB) {
            return 0;
        }

        return ($sortDirection === 'asc' ? $valA < $valB : $valA > $valB) ? -1 : 1;
    });
}
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
        position: relative; /* Para o posicionamento da seta */
    }
    th a {
        color: inherit;
        text-decoration: none;
        display: block; /* Faz o link preencher toda a célula */
    }
    th a:hover {
        text-decoration: underline;
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

    /* Estilo para o texto da meta */
    @import url('https://fonts.googleapis.com/css2?family=Comic+Neue:wght@700&display=swap');
    .fun-goal-text {
        font-family: 'Comic Neue', cursive;
        color: #ff6b6b; /* Uma cor divertida, como um vermelho-rosado */
        font-size: 1.2em; /* Um pouco maior */
        text-shadow: 1px 1px 1px #ccc; /* Uma leve sombra */
    }
</style>

<h1><?= __('Players Score') ?></h1>

<div class="filter-form-container">
    <?= $this->Form->create(null, ['type' => 'get', 'style' => 'display: flex; gap: 8px; align-items: center;']) ?>
        <?= $this->Form->select('cycle', $cycleOptions, ['default' => $selectedCycleOffset]) ?>
        <?= $this->Form->button(__('Filter')) ?>
    <?= $this->Form->end() ?>
</div>

<!-- Exibição das Metas -->
<div class="d-flex justify-content-center mb-3">
    <div class="text-center">
        <p class="fun-goal-text">
            Current Goal <?= $this->Number->format($minimumChestScore ?? 0) ?> chest points and <?= $this->Number->format($minimumEpicChestScore ?? 0) ?> Epic chest points
        </p>
    </div>
</div>

<h5><?= $cycleOptions[$selectedCycleOffset] ?></h5>
<br>

<?php if (!empty($playerChestCounts)): ?>
    <table>
        <thead>
            <tr>
                <th><?= $createSortLink('player', __('Player')) ?></th>
                <th><?= $createSortLink('total_chests', __('Total Chests')) ?></th>
                <th><?= $createSortLink('final_score', __('Final Score')) ?></th>
                <th><?= $createSortLink('epic_crypt_score', __('Epic Crypt Score')) ?></th>
                <?php
                if (isset($sourcesWithNonZeroScore) && !empty($sourcesWithNonZeroScore)) {
                    foreach ($sourcesWithNonZeroScore as $source): ?>
                        <th><?= $createSortLink($source, $source) ?></th>
                <?php endforeach;
                } ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($playersData as $playerData): ?>
                <tr>
                    <td>
                        <?= $this->Html->link(
                            $playerData['player'],
                            ['controller' => 'PlayerCycleSummaries', 'action' => 'playerHistory', urlencode($playerData['player'])]
                        ) ?>
                    </td>
                    <td><?= $playerData['total_chests'] ?></td>
                    <?php
                    $score = $playerData['final_score'];
                    $percentage = $minimumChestScore > 0 ? min(max($score / $minimumChestScore, 0), 1) : ($score > 0 ? 1 : 0);

                    // Ponto de início da transição (ex: 0.9 para 90%)
                    $transitionStart = (float)($scoreColorsConfig['score_color_transition_start'] ?? 0.0);

                    // Ajusta a porcentagem para a transição
                    $adjustedPercentage = 0;
                    if ($percentage >= $transitionStart) {
                        if (1.0 - $transitionStart > 0) { // Evita divisão por zero
                            $adjustedPercentage = ($percentage - $transitionStart) / (1.0 - $transitionStart);
                        } else {
                            $adjustedPercentage = 1.0; // Atingiu o máximo
                        }
                    }
                    
                    // Cores de início (pontuação baixa)
                    $startR = (int)($scoreColorsConfig['score_color_start_r'] ?? 255);
                    $startG = (int)($scoreColorsConfig['score_color_start_g'] ?? 0);
                    $startB = (int)($scoreColorsConfig['score_color_start_b'] ?? 0);

                    // Cores de fim (pontuação alta)
                    $endR = (int)($scoreColorsConfig['score_color_end_r'] ?? 0);
                    $endG = (int)($scoreColorsConfig['score_color_end_g'] ?? 255);
                    $endB = (int)($scoreColorsConfig['score_color_end_b'] ?? 0);

                    // Interpolação linear para cada componente de cor
                    $r = (int)($startR + ($endR - $startR) * $adjustedPercentage);
                    $g = (int)($startG + ($endG - $startG) * $adjustedPercentage);
                    $b = (int)($startB + ($endB - $startB) * $adjustedPercentage);

                    $color = sprintf('rgb(%d, %d, %d)', $r, $g, $b);
                    ?>
                    <td style="color: <?= $color ?>;"><?= $score ?></td>

                    <?php
                    $epicCryptScore = $playerData['epic_crypt_score'];

                    // Aplicar a mesma lógica de cor para a pontuação da Epic Crypt
                    $epicPercentage = $minimumEpicChestScore > 0 ? min(max($epicCryptScore / $minimumEpicChestScore, 0), 1) : ($epicCryptScore > 0 ? 1 : 0);
                    $epicAdjustedPercentage = 0;
                    if ($epicPercentage >= $transitionStart) {
                        if (1.0 - $transitionStart > 0) {
                            $epicAdjustedPercentage = ($epicPercentage - $transitionStart) / (1.0 - $transitionStart);
                        } else {
                            $epicAdjustedPercentage = 1.0;
                        }
                    }
                    
                    $epicR = (int)($startR + ($endR - $startR) * $epicAdjustedPercentage);
                    $epicG = (int)($startG + ($endG - $startG) * $epicAdjustedPercentage);
                    $epicB = (int)($startB + ($endB - $startB) * $epicAdjustedPercentage);
                    $epicColor = sprintf('rgb(%d, %d, %d)', $epicR, $epicG, $epicB);
                    ?>
                    <td style="color: <?= $epicColor ?>;"><?= $epicCryptScore ?></td>

                    <?php 
                    if (isset($sourcesWithNonZeroScore) && !empty($sourcesWithNonZeroScore)) {
                        foreach ($sourcesWithNonZeroScore as $source): ?>
                            <td><?= $playerData['counts'][$source] ?? 0 ?></td>
                    <?php endforeach;
                    } ?>
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

<?php if (isset($lastUpdate)): ?>
<div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e0e0e0; text-align: center; font-size: 0.9em; color: #666;">
    <p><?= __('Last update: {0} UTC', $lastUpdate->collected_at->i18nFormat('dd/MM/yyyy HH:mm:ss')) ?></p>
</div>
<?php endif; ?>