<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Type\Input;

use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\ScalarType;

class BooleanInputType extends InputType
{
    public const NAME = 'BooleanInput';

    protected static function getName(): string
    {
        return self::NAME;
    }

    protected static function getInternalType(): ScalarType
    {
        return new BooleanType();
    }
}
