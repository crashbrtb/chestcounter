<?php
/**
 * @var \App\View\AppView $this
 * @var array $playersData
 * @var string[] $playerNames
 * @var \Cake\I18n\FrozenDate[] $cycleDates
 */
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
</style>
<h3><?= __('Last 6 Cycles History') ?></h3>
<div class="table-responsive">
    <table>
        <thead>
                <tr>
                    <th><?= __('Player Name') ?></th>
                    <?php foreach ($cycleDates as $date): ?>
                        <th><?= h($date->i18nFormat('dd/MM/yyyy')) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get the last 4 cycle dates for the performance check
                $lastFourCycleDates = array_slice($cycleDates, -4);
                ?>
                <?php foreach ($playerNames as $playerName): ?>
                <tr>
                    <?php
                        // Calculate missed goals in the last 4 cycles
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

                        // Determine the color based on missed goals
                        $playerColor = 'red'; // Default to red
                        if ($missedGoalCount === 0) {
                            $playerColor = 'green';
                        } elseif ($missedGoalCount === 1) {
                            $playerColor = '#E3B40E'; // A shade of yellow/gold
                        }
                    ?>
                    <td style="color: <?= $playerColor ?>;"><?= h($playerName) ?></td>
                    <?php foreach ($cycleDates as $date): ?>
                        <?php
                            $dateKey = $date->toDateString();
                            $score = $playersData[$playerName][$dateKey] ?? 0;

                            $percentage = $minimumChestScore > 0 ? min(max($score / $minimumChestScore, 0), 1) : ($score > 0 ? 1 : 0);
                            $transitionStart = (float)($scoreColorsConfig['score_color_transition_start'] ?? 0.0);
                            $adjustedPercentage = 0;
                            if ($percentage >= $transitionStart) {
                                if (1.0 - $transitionStart > 0) {
                                    $adjustedPercentage = ($percentage - $transitionStart) / (1.0 - $transitionStart);
                                } else {
                                    $adjustedPercentage = 1.0;
                                }
                            }
                            $startR = (int)($scoreColorsConfig['score_color_start_r'] ?? 255);
                            $startG = (int)($scoreColorsConfig['score_color_start_g'] ?? 0);
                            $startB = (int)($scoreColorsConfig['score_color_start_b'] ?? 0);
                            $endR = (int)($scoreColorsConfig['score_color_end_r'] ?? 0);
                            $endG = (int)($scoreColorsConfig['score_color_end_g'] ?? 255);
                            $endB = (int)($scoreColorsConfig['score_color_end_b'] ?? 0);
                            $r = (int)($startR + ($endR - $startR) * $adjustedPercentage);
                            $g = (int)($startG + ($endG - $startG) * $adjustedPercentage);
                            $b = (int)($startB + ($endB - $startB) * $adjustedPercentage);
                            $color = sprintf('rgb(%d, %d, %d)', $r, $g, $b);
                        ?>
                        <td style="color: <?= $score > 0 ? $color : 'inherit' ?>;">
                            <?= $score > 0 ? $this->Number->format($score) : '-' ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
