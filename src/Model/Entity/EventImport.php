<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * One ranking uploaded for an event.
 *
 * Every upload is kept. Only one per event is live at a time: the newest draft,
 * or the one that was published. Older drafts are marked superseded so the
 * history of who sent what survives a correction.
 *
 * @property int $id
 * @property int $event_id
 * @property int|null $user_id
 * @property int|null $api_token_id
 * @property string $status
 * @property string|null $game_event_name
 * @property \Cake\I18n\DateTime|null $game_event_at
 * @property string $capture_method
 * @property string|null $client_version
 * @property string $payload_hash
 * @property int $row_count
 * @property \Cake\I18n\DateTime|null $created
 * @property array<\App\Model\Entity\EventImportRow> $event_import_rows
 * @property \App\Model\Entity\User|null $user
 */
class EventImport extends Entity
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUPERSEDED = 'superseded';
    public const STATUS_PUBLISHED = 'published';

    public const METHOD_PACKET = 'packet';
    public const METHOD_OCR = 'ocr';
    public const METHOD_CSV = 'csv';

    /**
     * Nothing is mass assignable: imports are built by EventImportService only.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [];

    /**
     * @return list<string>
     */
    public static function captureMethods(): array
    {
        return [self::METHOD_PACKET, self::METHOD_OCR, self::METHOD_CSV];
    }
}
