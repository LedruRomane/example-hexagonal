<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Type;

use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

class DateType extends ScalarType implements AliasedInterface
{
    public const FORMAT = 'Y-m-d';

    public string $name = 'Date';

    public ?string $description = 'A RFC3339 (ISO8601 compliant "Y-m-d") formatted date without time info';

    public function serialize($value): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(self::FORMAT);
        }

        throw new InvariantViolation('Unable to serialize.');
    }

    public function parseValue($value): ?\DateTime
    {
        if (null === $value) {
            return null;
        }

        \assert(\is_string($value));

        $date = \DateTime::createFromFormat(self::FORMAT, $value);

        if (false === $date) {
            throw new InvariantViolation('Unable to parse value.');
        }

        return $date;
    }

    public function parseLiteral($valueNode, array $variables = null): ?\DateTime
    {
        if ($valueNode instanceof StringValueNode) {
            return $this->parseValue($valueNode->value);
        }

        return null;
    }

    public static function getAliases(): array
    {
        return ['Date'];
    }
}
