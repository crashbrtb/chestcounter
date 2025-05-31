<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreatePlayerCycleSummaries extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('player_cycle_summaries');
        $table->addColumn('player_name', 'string', [ // Ou player_id se referenciar uma tabela de jogadores
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('cycle_start_date', 'date', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('cycle_end_date', 'date', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('total_chests', 'integer', [
            'default' => 0,
            'null' => false,
        ]);
        $table->addColumn('total_score', 'integer', [
            'default' => 0,
            'null' => false,
        ]);
        $table->addColumn('goal_achieved', 'boolean', [
            'default' => false,
            'null' => false,
        ]);
        $table->addColumn('fine_due', 'boolean', [ // Indica se uma multa é devida
            'default' => false,
            'null' => false,
        ]);
        $table->addColumn('fine_paid', 'boolean', [ // Indica se a multa devida foi paga
            'default' => false,
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'null' => false,
            'update' => 'CURRENT_TIMESTAMP',
        ]);
        // Adicionar um índice único para evitar duplicatas por jogador e ciclo
        $table->addIndex(['player_name', 'cycle_start_date'], ['unique' => true, 'name' => 'UNIQUE_PLAYER_CYCLE']);
        $table->create();
    }
}