<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Event\EventInterface;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Troops Model
 *
 * Catalogue of unit variants used by the stack calculator.
 *
 * @method \App\Model\Entity\Troop newEmptyEntity()
 * @method \App\Model\Entity\Troop newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Troop> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Troop get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Troop findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Troop patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Troop> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Troop|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Troop saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Troop>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Troop>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Troop>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Troop> saveManyOrFail(iterable $entities, array $options = [])
 */
class TroopsTable extends Table
{
    /**
     * Unit classes recognised by the calculator.
     *
     * @var list<string>
     */
    public const CLASSES = ['guardsman', 'specialist', 'monster', 'engineer'];

    /**
     * Combat categories. The first four take part in the kill order; siege and
     * scout are handled separately.
     *
     * @var list<string>
     */
    public const CATEGORIES = ['melee', 'mounted', 'ranged', 'flying', 'siege', 'scout', 'epic_monster_hunter'];

    /**
     * Army limits a unit can consume.
     *
     * @var list<string>
     */
    public const COST_POOLS = ['leadership', 'authority', 'dominance'];

    /**
     * Unit types eligible for the Monsters Boost research.
     *
     * @var list<string>
     */
    public const BOOSTABLE_TYPES = ['beast', 'giant', 'dragon', 'elemental'];

    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('troops');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Encode the bonus map before it reaches the database, so callers may pass
     * either an array or a ready-made JSON string.
     *
     * @param \Cake\Event\EventInterface $event The event.
     * @param \ArrayObject $data The data being marshalled.
     * @param \ArrayObject $options The marshalling options.
     * @return void
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        foreach (['bonuses', 'guardsmen_tiers'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = json_encode($data[$field], JSON_UNESCAPED_UNICODE);
            }
        }
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('slug')
            ->maxLength('slug', 80)
            ->requirePresence('slug', 'create')
            ->notEmptyString('slug');

        $validator
            ->scalar('name')
            ->maxLength('name', 80)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->inList('troop_class', self::CLASSES)
            ->requirePresence('troop_class', 'create');

        $validator
            ->inList('category', self::CATEGORIES)
            ->requirePresence('category', 'create');

        $validator
            ->inList('cost_pool', self::COST_POOLS)
            ->requirePresence('cost_pool', 'create');

        $validator
            ->integer('level')
            ->range('level', [1, 9]);

        $validator
            ->nonNegativeInteger('strength')
            ->nonNegativeInteger('health')
            ->greaterThan('cost', 0, 'A unit must consume at least one point of its army limit.');

        return $validator;
    }

    /**
     * Units available to the calculator, ordered so the UI groups them
     * predictably.
     *
     * @param \Cake\ORM\Query\SelectQuery $query Base query.
     * @param array<string, mixed> $options Unused.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findForCalculator(SelectQuery $query, array $options = []): SelectQuery
    {
        return $query
            ->where(['Troops.enabled' => true])
            ->orderBy([
                'Troops.troop_class' => 'ASC',
                'Troops.level' => 'DESC',
                'Troops.category' => 'ASC',
                'Troops.name' => 'ASC',
            ]);
    }
}
