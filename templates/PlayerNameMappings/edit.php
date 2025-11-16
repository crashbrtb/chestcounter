<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\PlayerNameMapping $playerNameMapping
 */
?>

<?php
$this->assign('title', __('Edit Player Name Mapping'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Player Name Mappings'), 'url' => ['action' => 'index']],
    ['title' => __('View'), 'url' => ['action' => 'view', $playerNameMapping->id]],
    ['title' => __('Edit')],
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
        <div class="mr-auto">
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $playerNameMapping->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $playerNameMapping->id), 'class' => 'btn btn-danger']
            ) ?>
        </div>
        <div class="ml-auto">
            <?= $this->Form->button(__('Save'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>
