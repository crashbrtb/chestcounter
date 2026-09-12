<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\EventAsset;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EventAssets Model
 *
 * @method \App\Model\Entity\EventAsset newEmptyEntity()
 * @method \App\Model\Entity\EventAsset newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EventAsset get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\EventAsset patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventAsset|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 */
class EventAssetsTable extends Table
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

        $this->setTable('event_assets');
        $this->setDisplayField('label');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Everything but the image bytes.
     *
     * @param \Cake\ORM\Query\SelectQuery $query The query.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findWithoutImage(SelectQuery $query): SelectQuery
    {
        return $query->select(
            $query->aliasFields(['id', 'slug', 'label', 'mime', 'created', 'modified'], $this->getAlias())
        );
    }

    /**
     * Replace the artwork for one of the two banner slots.
     *
     * Upsert rather than update: an installation that predates the seeding, or
     * one whose row was removed, should still end up with a working banner
     * instead of silently doing nothing.
     *
     * @param string $slug Which banner: event-live or no-event.
     * @param string $bytes Raw image bytes.
     * @param string $mime Image mime type.
     * @return bool Whether the row was written.
     */
    public function replaceImage(string $slug, string $bytes, string $mime): bool
    {
        $asset = $this->find()->where(['slug' => $slug])->first();
        if ($asset === null) {
            $asset = $this->newEmptyEntity();
            $asset->set('slug', $slug);
            $asset->set('label', self::defaultLabels()[$slug] ?? $slug);
        }

        $asset->set('mime', $mime);
        $asset->set('image', $bytes);

        return (bool)$this->save($asset);
    }

    /**
     * Labels used when a slot has to be created from scratch.
     *
     * @return array<string, string>
     */
    public static function defaultLabels(): array
    {
        return [
            EventAsset::SLUG_EVENT_LIVE => 'Banner shown while an event is running',
            EventAsset::SLUG_NO_EVENT => 'Banner shown when no event is running',
        ];
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
            ->maxLength('slug', 40)
            ->requirePresence('slug', 'create')
            ->notEmptyString('slug');

        $validator
            ->scalar('mime')
            ->maxLength('mime', 60)
            ->notEmptyString('mime');

        return $validator;
    }
}
