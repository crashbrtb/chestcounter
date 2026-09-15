<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\EventImport;
use Cake\ORM\Table;

/**
 * EventImports Model
 *
 * @property \App\Model\Table\EventsTable&\Cake\ORM\Association\BelongsTo $Events
 * @property \App\Model\Table\EventImportRowsTable&\Cake\ORM\Association\HasMany $EventImportRows
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @method \App\Model\Entity\EventImport newEmptyEntity()
 * @method \App\Model\Entity\EventImport|null find(string $type = 'all', mixed ...$args)
 */
class EventImportsTable extends Table
{
    /**
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('event_imports');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp', [
            'events' => ['Model.beforeSave' => ['created' => 'new']],
        ]);

        $this->belongsTo('Events', [
            'foreignKey' => 'event_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('EventImportRows', [
            'foreignKey' => 'event_import_id',
            'dependent' => true,
            'sort' => ['EventImportRows.position' => 'ASC'],
        ]);
    }

    /**
     * The upload an event is currently built from: the published one, otherwise
     * the newest draft.
     *
     * @param int $eventId Event id.
     * @return \App\Model\Entity\EventImport|null
     */
    public function current(int $eventId): ?EventImport
    {
        /** @var \App\Model\Entity\EventImport|null $import */
        $import = $this->find()
            ->where([
                'EventImports.event_id' => $eventId,
                'EventImports.status IN' => [EventImport::STATUS_DRAFT, EventImport::STATUS_PUBLISHED],
            ])
            ->contain(['EventImportRows' => ['Members'], 'Users'])
            ->orderBy(['EventImports.status' => 'DESC', 'EventImports.id' => 'DESC'])
            ->first();

        return $import;
    }
}
