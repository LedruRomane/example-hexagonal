<?php

declare(strict_types=1);

namespace App\Domain\Common\Exception;

/**
 * In web context, a NotFoundException is transformed into a 404 response if not caught.
 */
class NotFoundException extends \RuntimeException
{
}
