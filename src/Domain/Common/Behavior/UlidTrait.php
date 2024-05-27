<?php

declare(strict_types=1);

namespace App\Domain\Common\Behavior;

use Symfony\Component\Uid\Ulid;

/**
 * Trait to handle common entity identity needs though a given, non-sequential, unique identifier (using a ULID).
 */
trait UlidTrait
{
    /** @internal for db performances purposes */
    private ?int $id = null;

    /*
     * For some reasons, fails with fixtures / Doctrine when readonly:
     *
     * In ReflectionReadonlyProperty.php line 48:
     * [LogicException]
     * Attempting to change readonly property App\Domain\Film\Support::$uid.
     */
    /* readonly */ private Ulid $uid;

    /**
     * @internal for debug purposes when inspecting the database
     */
    private string $uid32;

    private function initIdentity(): void
    {
        $this->uid = new Ulid();
    }

    public function getUid(): Ulid
    {
        return $this->uid;
    }

    /**
     * @internal For specific, non-applicative, needs only (perfs, querying, database imports, …).
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUidAsString(): string
    {
        return (string) $this->uid;
    }

    /**
     * @internal for debug purposes when inspecting the database
     */
    public function computeDebugUid32(): void
    {
        $this->uid32 = $this->uid->toBase32();
    }
}
