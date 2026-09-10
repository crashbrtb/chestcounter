<?php
/**
 * Shown when a required row of the `config` table is missing or invalid.
 *
 * @var \App\View\AppView $this
 * @var string $param
 * @var string $reason
 */
?>
<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Configuração incompleta</h3>
            </div>
            <div class="card-body">
                <p>
                    O parâmetro <strong><?= h($param) ?></strong> está
                    <?= h($reason) ?> na tabela <code>config</code>, então a
                    pontuação não pode ser calculada.
                </p>
                <p>Se esta é uma instalação nova, execute o seed inicial:</p>
                <pre>php bin/cake.php migrations seed --seed InitialDataSeed</pre>
                <p>
                    Depois ajuste os parâmetros em
                    <?= $this->Html->link('Configurações', ['controller' => 'Config', 'action' => 'index']) ?>.
                </p>
            </div>
        </div>
    </div>
</div>
