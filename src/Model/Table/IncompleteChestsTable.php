<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\IncompleteChest;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * IncompleteChests Model
 *
 * The queue of chests the collector could not read. Rows are never deleted once
 * reviewed - a corrected chest keeps its row, pointing at the collected_chests
 * row it became, so the history of what went wrong stays intact.
 *
 * @method \App\Model\Entity\IncompleteChest newEmptyEntity()
 * @method \App\Model\Entity\IncompleteChest newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\IncompleteChest get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\IncompleteChest patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\IncompleteChest|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\IncompleteChest saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 */
class IncompleteChestsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('incomplete_chests');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');
    }

    /**
     * Default validation rules.
     *
     * Deliberately loose: these rows arrive precisely because something could
     * not be read, so every field is allowed to be empty on the way in. What a
     * person types when correcting is validated by `validationCorrection`.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 50)
            ->allowEmptyString('name');

        $validator
            ->scalar('player')
            ->maxLength('player', 50)
            ->allowEmptyString('player');

        $validator
            ->scalar('source')
            ->maxLength('source', 50)
            ->allowEmptyString('source');

        $validator
            ->scalar('status')
            ->inList('status', [
                IncompleteChest::STATUS_PENDING,
                IncompleteChest::STATUS_CORRECTED,
                IncompleteChest::STATUS_UNRESOLVED,
            ]);

        $validator
            ->scalar('review_notes')
            ->maxLength('review_notes', 255)
            ->allowEmptyString('review_notes');

        return $validator;
    }

    /**
     * Rules for the three fields a person fills in when correcting a chest.
     *
     * All three are required here, because that is the whole point: the row only
     * moves to collected_chests when it is complete.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationCorrection(Validator $validator): Validator
    {
        foreach (['name', 'player', 'source'] as $field) {
            $validator
                ->scalar($field)
                ->maxLength($field, 50)
                ->requirePresence($field)
                ->notEmptyString($field, __('Fill in the chest name, the player and the source.'));
        }

        return $validator;
    }

    /**
     * Only the chests still waiting to be looked at, oldest first.
     *
     * @param \Cake\ORM\Query\SelectQuery $query The query to modify.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findPending(SelectQuery $query): SelectQuery
    {
        return $query
            ->where(['status' => IncompleteChest::STATUS_PENDING])
            ->orderBy(['collected_at' => 'ASC']);
    }

    /**
     * Every column except the image.
     *
     * Listing screens must never select the blob: a page of twenty rows would
     * drag megabytes out of the database to show none of it. The image has its
     * own action, which fetches one row.
     *
     * @param \Cake\ORM\Query\SelectQuery $query The query to modify.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findWithoutImage(SelectQuery $query): SelectQuery
    {
        $fields = array_diff($this->getSchema()->columns(), ['screenshot']);

        return $query->select($fields);
    }

    /**
     * How many chests are still waiting.
     *
     * @return int
     */
    public function pendingCount(): int
    {
        return $this->find('pending')->count();
    }
}
