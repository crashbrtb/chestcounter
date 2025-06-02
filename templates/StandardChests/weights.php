<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\StandardChest[]|\Cake\Collection\CollectionInterface $standardChests
 * @var \App\Model\Entity\Config $referencegoalConfig
 * @var string $showAll // Espera-se que o controller passe '0' ou '1'
 */

// Define o estado atual e o link/texto do botão
$showAllParam = $this->request->getQuery('show_all', '0'); // Default é '0' (não mostrar todos)

$buttonLinkParams = $this->request->getQueryParams();
$buttonText = '';
// $buttonIcon = ''; // Ícone opcional

if ($showAllParam === '1') {
    $buttonText = __('Show Only Scored');
    // $buttonIcon = 'fas fa-filter';
    // Para voltar ao padrão, removemos o parâmetro ou definimos como 0
    // Se houver outros parâmetros de paginação/sort, eles são mantidos.
    unset($buttonLinkParams['show_all']);
    // Ou, se preferir manter o parâmetro explicitamente:
    // $buttonLinkParams['show_all'] = '0';
} else {
    $buttonText = __('Show All Chests');
    // $buttonIcon = 'fas fa-list-ul';
    $buttonLinkParams['show_all'] = '1';
}
// Garante que 'page' seja resetado ao mudar o filtro, para evitar ir para uma página inexistente.
if (isset($buttonLinkParams['page'])) {
    unset($buttonLinkParams['page']);
}
// Tentativa de correção para URL duplicada, assegurando que nenhum prefixo de rota seja aplicado automaticamente:
$toggleShowAllLink = $this->Url->build(['prefix' => false, 'controller' => 'StandardChests', 'action' => 'weights', '?' => $buttonLinkParams]);
?>



<style>
@import url('https://fonts.googleapis.com/css2?family=Comic+Neue:wght@700&display=swap');

.fun-goal-text {
    font-family: 'Comic Neue', cursive;
    color: #ff6b6b; /* Uma cor divertida, como um vermelho-rosado */
    font-size: 1.2em; /* Um pouco maior */
    text-shadow: 1px 1px 1px #ccc; /* Uma leve sombra */
}
</style>

<div class="card card-primary card-outline">
    <div class="card-header d-flex flex-column flex-md-row">
        <h2 class="card-title">
            <p class="fun-goal-text">Current goal: <?= $referencegoalConfig->value ?> points</p>
            <!-- -->
        </h2>
        <div class="d-flex ml-auto align-items-center">
            <?= $this->Html->link($buttonText, $toggleShowAllLink, ['class' => 'btn btn-info btn-sm mr-2']) ?>
            <?= $this->Paginator->limitControl([], null, [
                'label' => false,
                'class' => 'form-control form-control-sm',
                'templates' => ['inputContainer' => '{{content}}']
            ]); ?>
        </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('source', __('Chests')) ?></th>
                    <th><?= $this->Paginator->sort('score', __('Score')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($standardChests as $standardChest) : ?>
                    <tr>
                        <td><?= h($standardChest->source) ?></td>
                        <td><?= $this->Number->format($standardChest->score) ?></td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- /.card-body -->
    <div class="card-footer d-flex flex-column flex-md-row">
        <div class="text-muted">
            <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
        </div>
        <ul class="pagination pagination-sm mb-0 ml-auto">
            <?= $this->Paginator->first('<i class="fas fa-angle-double-left"></i>', ['escape' => false]) ?>
            <?= $this->Paginator->prev('<i class="fas fa-angle-left"></i>', ['escape' => false]) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next('<i class="fas fa-angle-right"></i>', ['escape' => false]) ?>
            <?= $this->Paginator->last('<i class="fas fa-angle-double-right"></i>', ['escape' => false]) ?>
        </ul>
    </div>
    <!-- /.card-footer -->
</div>