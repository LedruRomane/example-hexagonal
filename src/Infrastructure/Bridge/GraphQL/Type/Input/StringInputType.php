<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Type\Input;

use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\StringType;

class StringInputType extends InputType
{
    public const NAME = 'StringInput';

    protected static function getName(): string
    {
        return self::NAME;
    }

    protected static function getInternalType(): ScalarType
    {
        return new StringType();
    }
}
