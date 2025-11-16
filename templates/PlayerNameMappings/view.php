<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\PlayerNameMapping $playerNameMapping
 */
?>

<?php
$this->assign('title', __('View Player Name Mapping'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Player Name Mappings'), 'url' => ['action' => 'index']],
    ['title' => __('View')],
]);
?>

<div class="view card card-primary card-outline">
    <div class="card-header d-sm-flex">
        <h2 class="card-title"><?= h($playerNameMapping->ocr_text) ?></h2>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <tr>
                <th><?= __('Ocr Text') ?></th>
                <td><?= h($playerNameMapping->ocr_text) ?></td>
            </tr>
            <tr>
                <th><?= __('Correct Name') ?></th>
                <td><?= h($playerNameMapping->correct_name) ?></td>
            </tr>
            <tr>
                <th><?= __('Id') ?></th>
                <td><?= $this->Number->format($playerNameMapping->id) ?></td>
            </tr>
            <tr>
                <th><?= __('Created') ?></th>
                <td><?= h($playerNameMapping->created) ?></td>
            </tr>
            <tr>
                <th><?= __('Modified') ?></th>
                <td><?= h($playerNameMapping->modified) ?></td>
            </tr>
        </table>
    </div>
    <div class="card-footer d-flex">
        <div class="d-flex ml-auto">
            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $playerNameMapping->id], ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $playerNameMapping->id], ['class' => 'btn btn-danger', 'confirm' => __('Are you sure you want to delete # {0}?', $playerNameMapping->id)]) ?>
            <?= $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
</div>