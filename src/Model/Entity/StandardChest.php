<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * StandardChest Entity
 *
 * @property int $id
 * @property string $source
 * @property string|null $alias
 * @property int $score
 * @property int $monster
 * @property int|null $qty_chest
 * @property string $display_name
 */
class StandardChest extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'source' => true,
        'alias' => true,
        'score' => true,
        'monster' => true,
        'qty_chest' => true,
    ];

    /**
     * Virtual fields exposed when the entity is serialized.
     *
     * @var list<string>
     */
    protected array $_virtual = ['display_name'];

    /**
     * Name used by reports: the alias when defined, the raw source otherwise.
     *
     * @return string
     */
    protected function _getDisplayName(): string
    {
        $alias = trim((string)($this->alias ?? ''));

        return $alias !== '' ? $alias : (string)$this->source;
    }
}
