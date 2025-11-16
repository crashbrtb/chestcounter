<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\PlayerNameMapping $playerNameMapping
 */
?>

<?php
$this->assign('title', __('Add Player Name Mapping'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Player Name Mappings'), 'url' => ['action' => 'index']],
    ['title' => __('Add')],
]);
?>

<div class="card card-primary card-outline">
    <?= $this->Form->create($playerNameMapping) ?>
    <div class="card-body">
        <?php
        echo $this->Form->control('ocr_text');
        echo $this->Form->control('correct_name');
        ?>
    </div>
    <div class="card-footer d-flex">
        <div class="ml-auto">
            <?= $this->Form->button(__('Save'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>
