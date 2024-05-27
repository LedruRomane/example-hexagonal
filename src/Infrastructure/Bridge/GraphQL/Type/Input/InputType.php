<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Type\Input;

use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

/**
 * Scalar type override to accept invalid input values regarding GraphQL types,
 * allowing further assertion using the Symfony Validator and custom error message.
 */
abstract class InputType extends ScalarType implements AliasedInterface
{
    private ScalarType $internal;

    public function __construct(array $config = [])
    {
        $this->internal = static::getInternalType();
        $this->name = static::getName();
        $this->description = $this->internal->description;

        parent::__construct($config);
    }

    public function serialize($value): mixed
    {
        return $this->internal->serialize($value);
    }

    public function parseValue($value): mixed
    {
        try {
            return $this->internal->parseValue($value);
        } catch (Error) {
            return $value;
        }
    }

    public function parseLiteral(Node $valueNode, array $variables = null): mixed
    {
        try {
            return $this->internal->parseLiteral($valueNode, $variables);
        } catch (Error) {
            return $valueNode;
        }
    }

    abstract protected static function getInternalType(): ScalarType;

    abstract protected static function getName(): string;

    public static function getAliases(): array
    {
        return [static::getName()];
    }
}
