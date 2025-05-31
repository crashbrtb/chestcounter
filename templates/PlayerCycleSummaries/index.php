<?php
/**
 * @var \App\View\AppView $this
 * @var array $summariesByCycle Array of player cycle summaries grouped by cycle start date string.
 * @var array $formattedCycleDates Array of formatted cycle start and end dates, keyed by cycle start date string.
 */

// Obter identidade do usuário e verificar se é admin
$identity = $this->request->getAttribute('identity');
$isLoggedIn = $identity !== null;
$isAdmin = false; // Inicia como falso

if ($isLoggedIn) {
    // Assumindo que a propriedade na entidade User que contém os roles associados é 'roles'
    // e que a entidade Role tem uma propriedade 'name' para o nome do role.
    $userAssociatedRoles = $identity->get('roles'); 

    if (!empty($userAssociatedRoles) && (is_array($userAssociatedRoles) || $userAssociatedRoles instanceof \Traversable)) {
        foreach ($userAssociatedRoles as $roleEntity) {
            if (is_object($roleEntity) && isset($roleEntity->name) && $roleEntity->name === 'admin') {
                $isAdmin = true;
                break; 
            }
        }
    }
}
// var_dump($identity->get('roles')); // Removido ou comentado o var_dump anterior
?>
<div class="playerCycleSummaries index content">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= __('Player Cycle Summaries') ?></h3>
            <?php if ($isAdmin): // Mostra botões de processamento apenas para admin ?>
            <div class="card-tools">
                <?= $this->Html->link('<i class="fas fa-sync-alt"></i> ' . __('Process Cycle (offset=1)'), [
                    'action' => 'processCycleSummaries', '?' => ['cycle_offset' => 1]
                ], ['class' => 'btn btn-sm btn-outline-secondary ml-2', 'escape' => false]) ?>                 
                <?= $this->Html->link('<i class="fas fa-sync-alt"></i> ' . __('Process Previous Cycle (offset=0)'), [
                    'action' => 'processCycleSummaries', '?' => ['cycle_offset' => 0]
                ], ['class' => 'btn btn-sm btn-outline-primary ml-2', 'escape' => false]) ?>
                <?= $this->Html->link('<i class="fas fa-cogs"></i> ' . __('Process Newest Completed Cycle'), [
                    'action' => 'processCycleSummaries'
                ], ['class' => 'btn btn-sm btn-primary', 'escape' => false]) ?>
            </div>
            <?php endif; // Fim da verificação $isAdmin para card-tools ?>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <?php if (empty($summariesByCycle)): ?>
                <div class="callout callout-info">
                    <h5><?= __('No Summaries Found') ?></h5>
                    <p><?= __('There are no player cycle summaries to display. You can try processing a cycle using the buttons above.') ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($formattedCycleDates as $cycleStartDateString => $cycleDates): ?>
                    <?php if (isset($summariesByCycle[$cycleStartDateString])): ?>
                        <div class="card card-outline card-info collapsed-card mt-3"> 
                            <div class="card-header">
                                <h4 class="card-title">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-plus"></i> 
                                    </button>
                                    <?= __('Cycle: {0} to {1}', $cycleDates['start']->i18nFormat('yyyy-MM-dd'), $cycleDates['end']->i18nFormat('yyyy-MM-dd')) ?>
                                </h4>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body p-0"> 
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th><?= __('Player Name') ?></th>
                                                <th class="text-center"><?= __('Total Chests') ?></th>
                                                <th class="text-center"><?= __('Total Score') ?></th>
                                                <th class="text-center"><?= __('Goal Achieved') ?></th>
                                                <th class="text-center"><?= __('Fine Status') ?></th> 
                                                <?php if ($isAdmin): // Mostra coluna Actions apenas para admin ?>
                                                <th class="actions text-right"><?= __('Actions') ?></th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($summariesByCycle[$cycleStartDateString] as $summary): ?>
                                            <tr>
                                                <td><?= h($summary->player_name) ?></td>
                                                <td class="text-center"><?= $this->Number->format($summary->total_chests) ?></td>
                                                <td class="text-center"><?= $this->Number->format($summary->total_score) ?></td>
                                                <td class="text-center">
                                                    <?= $summary->goal_achieved ? '<span class="badge badge-success">'.__('Yes').'</span>' : '<span class="badge badge-danger">'.__('No').'</span>' ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if (!$summary->fine_due): ?>
                                                        <span class="badge badge-light"><?= __('N/A') ?></span>
                                                    <?php elseif ($summary->fine_paid): ?>
                                                        <span class="badge badge-success"><?= __('Paid') ?></span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning"><?= __('Due') ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <?php if ($isAdmin): // Mostra coluna Actions apenas para admin ?>
                                                <td class="actions text-right">
                                                    <?= $this->Html->link('<i class="fas fa-eye"></i> ' . __('View'), ['action' => 'view', $summary->id], ['class' => 'btn btn-xs btn-info', 'escape' => false]) ?>
                                                    <?php if ($summary->fine_due && !$summary->fine_paid): ?>
                                                        <?= $this->Form->postLink('<i class="fas fa-check-circle"></i> ' . __('Mark Paid'), 
                                                            ['action' => 'markFinePaid', $summary->id], 
                                                            ['confirm' => __('Are you sure you want to mark the fine as paid for {0}?', $summary->player_name), 'class' => 'btn btn-xs btn-success ml-1', 'escape' => false]
                                                        ) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <?php endif; ?>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /.table-responsive -->
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
</div>
