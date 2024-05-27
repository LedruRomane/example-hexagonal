<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Type;

use App\Infrastructure\Bridge\GraphQL\Error\NotFoundError;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Symfony\Component\Uid\Ulid;

class ULIDType extends ScalarType implements AliasedInterface
{
    public string $name = 'ULID';

    public ?string $description = 'A unique identifier in ULID format (canonical base-32 encoding)';

    public static function getAliases(): array
    {
        return ['ULID'];
    }

    public function serialize($value): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof Ulid) {
            return $value->toBase32();
        }

        throw new InvariantViolation('Unable to serialize.');
    }

    public function parseValue($value): ?Ulid
    {
        if (null === $value) {
            return null;
        }

        \assert(\is_string($value));

        try {
            return Ulid::fromBase32($value);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundError('Invalid ULID format.', $e);
        }
    }

    public function parseLiteral(Node $valueNode, array $variables = null): ?Ulid
    {
        if ($valueNode instanceof StringValueNode) {
            return $this->parseValue($valueNode->value);
        }

        return null;
    }
}
