<?php

declare(strict_types=1);

namespace App\Domain\Helper;

interface UuidGeneratorInterface
{
    public function generate(): string;
}
