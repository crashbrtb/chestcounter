<?php
/**
 * @var \App\View\AppView $this
 * @var string[] $playerList
 * @var string $title
 */
?>

<div class="collectedChests index content">
    <h2><?= h($title) ?></h2>

    <?= $this->Flash->render() ?>

    <p>
        <?= __('Select the player name you want to keep (Correct Name) and the player name you want to replace (Incorrect Name). All records for the incorrect name will be updated to the correct name.') ?>
    </p>

    <?= $this->Form->create(null, ['url' => ['action' => 'mergePlayers']]) ?>
    <fieldset>
        <legend><?= __('Merge Player Names') ?></legend>
        <?php
            echo $this->Form->control('correct_player_name', [
                'options' => $playerList,
                'empty' => __('Select Correct Name'),
                'label' => __('Correct Player Name (Keep this one)'),
                'class' => 'form-control mb-2 mr-sm-2 mb-sm-0' // Exemplo de classes Bootstrap, ajuste conforme seu tema
            ]);
            echo $this->Form->control('incorrect_player_name', [
                'options' => $playerList,
                'empty' => __('Select Incorrect Name'),
                'label' => __('Incorrect Player Name (Replace this one)'),
                'class' => 'form-control mb-2 mr-sm-2 mb-sm-0' // Exemplo de classes Bootstrap, ajuste conforme seu tema
            ]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Merge Players'), ['class' => 'btn btn-primary mt-2']) ?>
    <?= $this->Form->end() ?>

    <hr>

    <h3><?= __('Current Unique Player List') ?></h3>
    <?php if (!empty($playerList)): ?>
        <ul>
            <?php foreach ($playerList as $player): ?>
                <li><?= h($player) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p><?= __('No player names found.') ?></p>
    <?php endif; ?>
</div>
