<?php
declare(strict_types=1);

namespace App\Service\Maintenance;

use RuntimeException;

/**
 * Raised when a database backup cannot be configured or cannot be taken.
 *
 * The message is written for whoever is reading it — the administrator on the
 * maintenance page, or the cron log the morning after — so it always says what
 * was attempted and what got in the way: a folder that cannot be written, a
 * missing `mysqldump`, or what the dump itself printed before it gave up.
 */
class BackupException extends RuntimeException
{
}
