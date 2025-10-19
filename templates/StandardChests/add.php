<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\StandardChest $standardChest
 */
?>

<?php
$this->assign('title', __('Add Standard Chest'));
$this->Breadcrumbs->add([
    ['title' => __('Home'), 'url' => '/'],
    ['title' => __('List Standard Chests'), 'url' => ['action' => 'index']],
    ['title' => __('Add')],
]);
?>

<div class="card card-primary card-outline">
    <?= $this->Form->create($standardChest, ['valueSources' => ['query', 'context']]) ?>
    <div class="card-body">
        <?php
            echo $this->Form->control('source', ['label' => 'Source']);
            echo $this->Form->control('score', ['label' => 'Score']);
        ?>
        <div class="form-group">
            <label for="monster">Epic Monster</label>
            <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="1 = Epic Monsters chest 0 = Regular chest"></i>
            <?= $this->Form->checkbox('monster', ['id' => 'monster', 'class' => 'form-check-input', 'required' => false]) ?>
        </div>
        <?php
            echo $this->Form->control('qty_chest', [
                'label' => 'Chests Qty <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="If the chest type is epic monsters, inform the amount of chests earned by killing a monster"></i>',
                'escape' => false,
            ]);
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