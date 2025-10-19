<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddMonsterAndQtyChestToStandardChests extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('standard_chests');
        $table->addColumn('monster', 'integer', [
            'default' => 0,
            'null' => false,
            'comment' => '1 = Epic Monsters chest 0 = Regular chest',
            'after' => 'score',
        ]);
        $table->addColumn('qty_chest', 'integer', [
            'default' => null,
            'null' => true,
            'comment' => 'If the chest type is epic monsters, inform the amount of chests earned by killing a monster',
            'after' => 'monster',
        ]);
        $table->update();
    }
}
