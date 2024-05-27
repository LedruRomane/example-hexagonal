<?php

declare(strict_types=1);

namespace App\Domain\Common\Behavior;

interface TimestampableInterface
{
    public function markAsUpdated(\DateTime $at = null): void;
}
