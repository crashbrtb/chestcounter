<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

/**
 * EventImportRows Model
 *
 * @property \App\Model\Table\EventImportsTable&\Cake\ORM\Association\BelongsTo $EventImports
 * @property \App\Model\Table\MembersTable&\Cake\ORM\Association\BelongsTo $Members
 */
class EventImportRowsTable extends Table
{
    /**
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('event_import_rows');
        $this->setDisplayField('raw_name');
        $this->setPrimaryKey('id');

        $this->belongsTo('EventImports', [
            'foreignKey' => 'event_import_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Members', [
            'foreignKey' => 'member_id',
        ]);
    }
}
