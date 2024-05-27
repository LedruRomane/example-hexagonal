<?php

declare(strict_types=1);

namespace App\Domain\Common\Exception;

/**
 * In web context, a ForbiddenException is transformed into a 403 response if not caught.
 */
class ForbiddenException extends \RuntimeException
{
}
