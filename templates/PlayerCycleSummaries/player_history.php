<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\PlayerCycleSummary> $playerHistory
 * @var string $playerName
 */
?>
<div class="playerCycleSummaries index content">
    <h3><?= __('History for Player: ') . h($playerName) ?></h3>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?= __('Total Score') ?></th>
                    <th><?= __('Cycle End Date') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($playerHistory as $summary): ?>
                <tr>
                    <?php
                    $score = $summary->total_score;
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
                    <td style="color: <?= $color ?>;"><?= $this->Number->format($score) ?></td>
                    <td><?= h($summary->cycle_end_date->i18nFormat('dd/MM/yyyy')) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
