<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Type;

use GraphQL\Type\Definition\EnumType;
use GraphQL\Utils\Utils;

/**
 * @see https://github.com/webonyx/graphql-php/blob/master/src/Type/Definition/PhpEnumType.php
 * @see https://github.com/webonyx/graphql-php/blob/7b59570e51f47309268ab1a5d36aabe0c9aae670/docs/type-definitions/enums.md#construction-from-php-enum
 * @see https://github.com/webonyx/graphql-php/issues/1192
 */
abstract class PhpEnumType extends EnumType
{
    /**
     * @var class-string<\UnitEnum>
     */
    protected string $enumClass;

    /**
     * @param class-string<\UnitEnum> $enum
     */
    public function __construct(string $enum, string $name)
    {
        $this->enumClass = $enum;
        $reflection = new \ReflectionEnum($enum);

        $enumDefinitions = [];
        foreach ($reflection->getCases() as $case) {
            $enumDefinitions[$case->name] = [
                'value' => $case->getValue(),
                'description' => '',
                'deprecationReason' => null,
            ];
        }

        parent::__construct([
            'name' => $name,
            'values' => $enumDefinitions,
            'description' => null,
        ]);
    }

    public function serialize($value): string
    {
        // @phpstan-ignore-next-line
        if (!is_a($value, $this->enumClass)) {
            $notEnum = Utils::printSafe($value);

            throw new \Exception("Cannot serialize value as enum: {$notEnum}, expected instance of {$this->enumClass}.");
            // throw new SerializationError("Cannot serialize value as enum: {$notEnum}, expected instance of {$this->enumClass}.");
        }

        return $value->name;
    }
}
