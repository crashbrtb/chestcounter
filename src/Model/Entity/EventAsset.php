<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * EventAsset Entity
 *
 * The banner artwork shown on the score page: one row for "an event is running",
 * one for "no event". Shipped with defaults, replaceable by an administrator.
 *
 * @property int $id
 * @property string $slug
 * @property string $label
 * @property string $mime
 * @property resource|string $image
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 */
class EventAsset extends Entity
{
    public const SLUG_EVENT_LIVE = 'event-live';
    public const SLUG_NO_EVENT = 'no-event';

    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'slug' => true,
        'label' => true,
        'mime' => true,
        'image' => true,
    ];

    /**
     * @var list<string>
     */
    protected array $_hidden = ['image'];
}
