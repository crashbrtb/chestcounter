<?php
declare(strict_types=1);

namespace App\Service\Branding;

use RuntimeException;

/**
 * Raised when an uploaded logo or favicon cannot be accepted.
 *
 * The message is written for the administrator standing in front of the upload
 * form, so it always says which rule the file broke and what the rule is.
 */
class BrandingImageException extends RuntimeException
{
}
