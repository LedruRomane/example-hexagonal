<?php

declare(strict_types=1);

namespace App\Domain\Common\Behavior;

/**
 * @see TimestampableInterface
 */
trait TimestampableTrait
{
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @interal Unit tests only, where Doctrine does not populate this field.
     */
    public function initCreated(\DateTime $at = null): void
    {
        $this->createdAt = $at ?? new \DateTime();
    }

    public function markAsUpdated(\DateTime $at = null): void
    {
        $this->updatedAt = $at ?? new \DateTime();
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }
}
