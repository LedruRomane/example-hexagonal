<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Type\Input;

use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ScalarType;

class IntInputType extends InputType
{
    public const NAME = 'IntInput';

    protected static function getName(): string
    {
        return self::NAME;
    }

    protected static function getInternalType(): ScalarType
    {
        return new IntType();
    }
}
