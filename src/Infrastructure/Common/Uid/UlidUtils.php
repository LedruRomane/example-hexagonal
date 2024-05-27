<?php

declare(strict_types=1);

namespace App\Infrastructure\Common\Uid;

use function Symfony\Component\String\s;

use Symfony\Component\Uid\Ulid;

class UlidUtils
{
    /**
     * Returns the identifier as a prefixed hexadecimal case insensitive string.
     *
     * @see https://github.com/symfony/symfony/pull/45945
     */
    public static function toHex(Ulid $ulid, bool $prefix = true): string
    {
        return ($prefix ? '0x' : '') . bin2hex($ulid->toBinary());
    }

    /**
     * Returns the identifier as a prefixed hexadecimal case insensitive string.
     *
     * @see https://github.com/symfony/symfony/pull/45945
     */
    public static function fromHex(string $hex): Ulid
    {
        return Ulid::fromString((string) @hex2bin((string) s($hex)->trimPrefix('0x')));
    }
}
