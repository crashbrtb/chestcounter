<?php
declare(strict_types=1);

namespace App\Service\Maintenance;

use RuntimeException;

/**
 * Raised when the server's crontab cannot be read or written.
 *
 * The message is written for the administrator looking at the maintenance page,
 * so it always says what was attempted and what the server answered: on a
 * shared host the answer is usually "you are not allowed to use crontab", and
 * that is something only the person reading can act on.
 */
class CrontabException extends RuntimeException
{
}
