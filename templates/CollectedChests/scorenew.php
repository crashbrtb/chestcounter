<?php
/**
 * @var \App\View\AppView $this
 * @var array $playerChestCounts
 * @var array $playerTotalChests
 * @var array $playerFinalScores
 * @var array $cycleOptions
 * @var string $selectedCycleOffset
 * @var int $minimumChestScore
 * @var int $minimumEpicChestScore
 * @var \Cake\I18n\FrozenTime|null $lastUpdate
 * @var array $currentCycleFormatted
 * @var string[] $sourcesWithNonZeroScore
 * @var array $chestScores
 * @var array $scoreColorsConfig
 */
?>
<?php
$sortColumn = $this->request->getQuery('sort', 'final_score');
$sortDirection = $this->request->getQuery('direction', 'desc');

$createSortLink = function ($column, $title) use ($sortColumn, $sortDirection) {
    $currentParams = $this->request->getQuery();
    $direction = ($sortColumn === $column && $sortDirection === 'asc') ? 'desc' : 'asc';
    $arrow = '';
    if ($sortColumn === $column) {
        $arrow = $sortDirection === 'asc' ? ' ▲' : ' ▼';
    }
    $url = $this->Url->build(['?' => array_merge($currentParams, ['sort' => $column, 'direction' => $direction])]);

    return '<a href="' . $url . '">' . h($title) . $arrow . '</a>';
};

$playersData = [];
if (!empty($playerChestCounts)) {
    $sourcesWithNonZeroScore = $sourcesWithNonZeroScore ?? [];
    usort($sourcesWithNonZeroScore, function ($a, $b) use ($chestScores) {
        $scoreA = isset($chestScores[$a]) ? (int)$chestScores[$a]->score : 0;
        $scoreB = isset($chestScores[$b]) ? (int)$chestScores[$b]->score : 0;

        if ($scoreA === $scoreB) {
            return strcasecmp((string)$a, (string)$b);
        }

        return $scoreB <=> $scoreA;
    });

    foreach (array_keys($playerChestCounts) as $player) {
        $counts = $playerChestCounts[$player] ?? [];
        $epicCryptScore = 0;
        $epicMonsterChestCount = 0;
        foreach ($counts as $source => $count) {
            if (stripos((string)$source, 'epic') !== false && isset($chestScores[$source])) {
                $epicCryptScore += $chestScores[$source]->score * $count;
            }
            if (isset($chestScores[$source]) && !empty($chestScores[$source]->monster)) {
                $epicMonsterChestCount += (int)$count;
            }
        }

        $playerData = [
            'player' => $player,
            'total_chests' => $playerTotalChests[$player] ?? 0,
            'final_score' => $playerFinalScores[$player] ?? 0,
            'epic_crypt_score' => $epicCryptScore,
            'epic_monster_chest_count' => $epicMonsterChestCount,
            'counts' => $counts,
        ];

        foreach ($sourcesWithNonZeroScore as $source) {
            $playerData[$source] = $counts[$source] ?? 0;
        }
        $playersData[] = $playerData;
    }
}

if (!empty($playersData)) {
    usort($playersData, function ($a, $b) use ($sortColumn, $sortDirection) {
        $valA = $a[$sortColumn] ?? 0;
        $valB = $b[$sortColumn] ?? 0;

        if ($sortColumn === 'player') {
            return $sortDirection === 'asc'
                ? strcasecmp((string)$valA, (string)$valB)
                : strcasecmp((string)$valB, (string)$valA);
        }

        if ($valA == $valB) {
            return 0;
        }

        return ($sortDirection === 'asc' ? $valA < $valB : $valA > $valB) ? -1 : 1;
    });
}

$topByEpicMonsters = $playersData;
usort($topByEpicMonsters, function ($a, $b) {
    return ($b['epic_monster_chest_count'] <=> $a['epic_monster_chest_count']);
});
$topByEpicMonsters = array_slice($topByEpicMonsters, 0, 5);

$topByMonsters = $playersData;
usort($topByMonsters, function ($a, $b) {
    return ($b['epic_crypt_score'] <=> $a['epic_crypt_score']);
});
$topByMonsters = array_slice($topByMonsters, 0, 5);

$transitionStart = (float)($scoreColorsConfig['score_color_transition_start'] ?? 0.0);
$startR = (int)($scoreColorsConfig['score_color_start_r'] ?? 255);
$startG = (int)($scoreColorsConfig['score_color_start_g'] ?? 0);
$startB = (int)($scoreColorsConfig['score_color_start_b'] ?? 0);
$endR = (int)($scoreColorsConfig['score_color_end_r'] ?? 0);
$endG = (int)($scoreColorsConfig['score_color_end_g'] ?? 255);
$endB = (int)($scoreColorsConfig['score_color_end_b'] ?? 0);

$scoreColor = function ($scoreValue, $targetValue) use ($transitionStart, $startR, $startG, $startB, $endR, $endG, $endB) {
    $percentage = $targetValue > 0
        ? min(max($scoreValue / $targetValue, 0), 1)
        : ($scoreValue > 0 ? 1 : 0);

    $adjusted = 0;
    if ($percentage >= $transitionStart) {
        $adjusted = (1.0 - $transitionStart > 0)
            ? ($percentage - $transitionStart) / (1.0 - $transitionStart)
            : 1.0;
    }

    $r = (int)($startR + ($endR - $startR) * $adjusted);
    $g = (int)($startG + ($endG - $startG) * $adjusted);
    $b = (int)($startB + ($endB - $startB) * $adjusted);

    return sprintf('rgb(%d, %d, %d)', $r, $g, $b);
};
?>

<style>
    .score-new-page {
        --bg: #f5f7fb;
        --card: #ffffff;
        --text: #1f2937;
        --muted: #6b7280;
        --line: #e5e7eb;
        --accent: #4f46e5;
        background: var(--bg);
        padding: 16px;
        border-radius: 14px;
    }

    .score-toolbar {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .score-title {
        margin: 0;
        color: var(--text);
        font-weight: 700;
    }

    .cycle-subtitle {
        color: var(--muted);
        margin: 4px 0 0;
        font-size: 0.95rem;
    }

    .goal-pill {
        background: #eef2ff;
        border: 1px solid #c7d2fe;
        color: #3730a3;
        border-radius: 999px;
        padding: 8px 14px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .filter-form {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .top-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 16px;
    }

    .top-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(17, 24, 39, 0.06);
        overflow: hidden;
    }

    .top-card-header {
        padding: 14px 16px;
        border-bottom: 1px solid var(--line);
        font-weight: 700;
        color: var(--text);
        background: #fcfcff;
    }

    .top-list {
        list-style: none;
        margin: 0;
        padding: 8px 0;
    }

    .top-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 10px 16px;
    }

    .top-item:not(:last-child) {
        border-bottom: 1px solid #f0f2f7;
    }

    .top-rank {
        min-width: 28px;
        height: 28px;
        border-radius: 999px;
        background: #e0e7ff;
        color: #3730a3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .top-player {
        flex: 1;
        color: var(--text);
        margin-left: 10px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .top-value {
        font-weight: 700;
        color: #111827;
    }

    .ranking-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(17, 24, 39, 0.06);
        overflow: hidden;
    }

    .ranking-header {
        padding: 14px 16px;
        border-bottom: 1px solid var(--line);
        font-weight: 700;
        color: var(--text);
        background: #fcfcff;
    }

    .ranking-table-wrap {
        overflow-x: auto;
    }

    .ranking-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }

    .ranking-table th,
    .ranking-table td {
        padding: 11px 12px;
        border-bottom: 1px solid #eef0f4;
        text-align: center;
        white-space: nowrap;
    }

    .ranking-table th {
        background: #f8fafc;
        color: #374151;
        font-size: 0.9rem;
        font-weight: 700;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .ranking-table th a {
        color: inherit;
        text-decoration: none;
    }

    .ranking-table th a:hover {
        color: var(--accent);
    }

    .ranking-table tbody tr:hover {
        background: #f8fbff;
    }

    .player-link {
        color: #1d4ed8;
        text-decoration: none;
        font-weight: 600;
    }

    .player-link:hover {
        text-decoration: underline;
    }

    .footer-note {
        margin-top: 14px;
        text-align: center;
        color: var(--muted);
        font-size: 0.9rem;
    }

    @media (max-width: 992px) {
        .top-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="score-new-page">
    <div class="score-toolbar">
        <div>
            <h1 class="score-title"><?= __('Players Score') ?></h1>
            <p class="cycle-subtitle"><?= h($cycleOptions[$selectedCycleOffset] ?? __('Current Cycle')) ?></p>
        </div>
        <div class="filter-form">
            <?= $this->Form->create(null, ['type' => 'get']) ?>
            <?= $this->Form->select('cycle', $cycleOptions, ['default' => $selectedCycleOffset]) ?>
            <?= $this->Form->button(__('Filter')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>

    <div class="goal-pill">
        <?= __('Current Goal {0} chest points and {1} Epic chest points', $this->Number->format($minimumChestScore ?? 0), $this->Number->format($minimumEpicChestScore ?? 0)) ?>
    </div>

    <div class="top-grid mt-3">
        <section class="top-card">
            <div class="top-card-header"><?= __('Top 5 Epic Monster Chest') ?></div>
            <?php if (!empty($topByEpicMonsters)): ?>
                <ul class="top-list">
                    <?php foreach ($topByEpicMonsters as $i => $player): ?>
                        <li class="top-item">
                            <span class="top-rank"><?= $i + 1 ?></span>
                            <span class="top-player"><?= h($player['player']) ?></span>
                            <span class="top-value"><?= (int)$player['epic_monster_chest_count'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="p-3 text-muted"><?= __('No data available for this cycle.') ?></div>
            <?php endif; ?>
        </section>

        <section class="top-card">
            <div class="top-card-header"><?= __('Top 5 - Epic Crypt Raiders') ?></div>
            <?php if (!empty($topByMonsters)): ?>
                <ul class="top-list">
                    <?php foreach ($topByMonsters as $i => $player): ?>
                        <li class="top-item">
                            <span class="top-rank"><?= $i + 1 ?></span>
                            <span class="top-player"><?= h($player['player']) ?></span>
                            <span class="top-value"><?= (int)$player['epic_crypt_score'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="p-3 text-muted"><?= __('No data available for this cycle.') ?></div>
            <?php endif; ?>
        </section>
    </div>

    <section class="ranking-card">
        <div class="ranking-header"><?= __('Full Ranking') ?></div>
        <?php if (!empty($playersData)): ?>
            <div class="ranking-table-wrap">
                <table class="ranking-table">
                    <thead>
                    <tr>
                        <th><?= __('Pos.') ?></th>
                        <th><?= $createSortLink('player', __('Player')) ?></th>
                        <th><?= $createSortLink('final_score', __('Final Score')) ?></th>
                        <th><?= $createSortLink('total_chests', __('Total Chests')) ?></th>
                        <th><?= $createSortLink('epic_crypt_score', __('Epic Crypt Score')) ?></th>
                        <?php foreach ($sourcesWithNonZeroScore as $source): ?>
                            <th><?= $createSortLink($source, $source) ?></th>
                        <?php endforeach; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($playersData as $index => $playerData): ?>
                        <?php
                        $score = $playerData['final_score'];
                        $scoreCellColor = $scoreColor($score, (int)$minimumChestScore);
                        $epicScore = $playerData['epic_crypt_score'];
                        $epicCellColor = $scoreColor($epicScore, (int)$minimumEpicChestScore);
                        ?>
                        <tr>
                            <td><span class="top-rank"><?= $index + 1 ?></span></td>
                            <td>
                                <?= $this->Html->link(
                                    $playerData['player'],
                                    ['controller' => 'PlayerCycleSummaries', 'action' => 'playerHistory', urlencode($playerData['player'])],
                                    ['class' => 'player-link']
                                ) ?>
                            </td>
                            <td style="color: <?= h($scoreCellColor) ?>; font-weight: 700;"><?= (int)$score ?></td>
                            <td><?= (int)$playerData['total_chests'] ?></td>
                            <td style="color: <?= h($epicCellColor) ?>; font-weight: 700;"><?= (int)$epicScore ?></td>
                            <?php foreach ($sourcesWithNonZeroScore as $source): ?>
                                <td><?= (int)($playerData['counts'][$source] ?? 0) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-3 text-muted"><?= __('No chests collected in this cycle.') ?></div>
        <?php endif; ?>
    </section>

    <div class="footer-note">
        <?php
        $now = \Cake\I18n\FrozenTime::now();
        $startOfCurrentCycle = \Cake\I18n\FrozenTime::parse($currentCycleFormatted['start'], 'UTC')->setTimezone('America/Sao_Paulo');
        $endOfCurrentCycle = \Cake\I18n\FrozenTime::parse($currentCycleFormatted['end'], 'UTC')->setTimezone('America/Sao_Paulo');

        if ($now >= $startOfCurrentCycle && $now <= $endOfCurrentCycle) {
            $diff = $now->diff($endOfCurrentCycle);
            echo __('Time remaining until the end of the Current Cycle: ');
            if ($diff->days > 0) {
                echo __("{0} day{1}, ", $diff->days, $diff->days > 1 ? 's' : '');
            }
            echo __("{0} hour{1}, ", $diff->h, $diff->h > 1 ? 's' : '');
            echo __("{0} minute{1}, ", $diff->i, $diff->i > 1 ? 's' : '');
            echo __("{0} second{1}", $diff->s, $diff->s > 1 ? 's' : '');
        } else {
            echo __('The Current Cycle has already ended.');
        }
        ?>
    </div>

    <?php if (isset($lastUpdate)): ?>
        <div class="footer-note">
            <?= __('Last update: {0} UTC', $lastUpdate->collected_at->i18nFormat('dd/MM/yyyy HH:mm:ss')) ?>
        </div>
    <?php endif; ?>
</div>
