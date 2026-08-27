<?php
/**
 * @var \App\View\AppView $this
 * @var array $playersData
 * @var string[] $playerNames
 * @var \Cake\I18n\FrozenDate[] $cycleDates
 * @var int $minimumChestScore
 * @var array $scoreColorsConfig
 */

$minimumChestScore = $minimumChestScore ?? 0;
$scoreColorsConfig = $scoreColorsConfig ?? [];

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
        display: inline-block;
        margin-bottom: 16px;
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

    .ranking-table tbody tr:hover {
        background: #f8fbff;
    }

    .player-link {
        text-decoration: none;
        font-weight: 600;
    }

    .player-link:hover {
        text-decoration: underline;
    }
</style>

<div class="score-new-page">
    <div class="score-toolbar">
        <div>
            <h1 class="score-title"><?= __('Last 6 Cycles History') ?></h1>
            <p class="cycle-subtitle"><?= __('Historical score breakdown across recent cycles') ?></p>
        </div>
    </div>

    <?php if ($minimumChestScore > 0): ?>
    <div class="goal-pill">
        <?= __('Chest Score Goal per cycle: {0}', $this->Number->format($minimumChestScore)) ?>
    </div>
    <?php endif; ?>

    <section class="ranking-card">
        <div class="ranking-header"><?= __('History Matrix') ?></div>
        <?php if (!empty($playerNames) && !empty($cycleDates)): ?>
            <div class="ranking-table-wrap">
                <table class="ranking-table">
                    <thead>
                        <tr>
                            <th class="text-left"><?= __('Player Name') ?></th>
                            <?php foreach ($cycleDates as $date): ?>
                                <th><?= h($date->i18nFormat('dd/MM/yyyy')) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $lastFourCycleDates = array_slice($cycleDates, -4); ?>
                        <?php foreach ($playerNames as $playerName): ?>
                        <tr>
                            <?php
                                $missedGoalCount = 0;
                                if (!empty($lastFourCycleDates)) {
                                    foreach ($lastFourCycleDates as $date) {
                                        $dateKey = $date->toDateString();
                                        $score = $playersData[$playerName][$dateKey] ?? 0;
                                        if ($score < $minimumChestScore) {
                                            $missedGoalCount++;
                                        }
                                    }
                                }

                                $playerColor = '#dc2626'; // Red
                                if ($missedGoalCount === 0) {
                                    $playerColor = '#16a34a'; // Green
                                } elseif ($missedGoalCount === 1) {
                                    $playerColor = '#d97706'; // Gold/Amber
                                }
                            ?>
                            <td class="text-left">
                                <?= $this->Html->link(
                                    $playerName,
                                    ['controller' => 'PlayerCycleSummaries', 'action' => 'playerHistory', urlencode($playerName)],
                                    ['class' => 'player-link', 'style' => 'color: ' . $playerColor . ';']
                                ) ?>
                            </td>
                            <?php foreach ($cycleDates as $date): ?>
                                <?php
                                    $dateKey = $date->toDateString();
                                    $score = $playersData[$playerName][$dateKey] ?? 0;
                                    $cellColor = $scoreColor($score, (int)$minimumChestScore);
                                ?>
                                <td style="color: <?= $score > 0 ? h($cellColor) : 'inherit' ?>; font-weight: <?= $score > 0 ? '700' : 'normal' ?>;">
                                    <?= $score > 0 ? $this->Number->format($score) : '-' ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-3 text-muted text-center"><?= __('No cycle history data available.') ?></div>
        <?php endif; ?>
    </section>
</div>
