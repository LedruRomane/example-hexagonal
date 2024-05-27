<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Type;

use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

class DateTimeType extends ScalarType implements AliasedInterface
{
    /**
     * Extended ISO8601 compliant format, with second fractions.
     * However, this format makes the second fractions mandatory,
     * so we should fallback on \DateTime::RFC3339 if it fails.
     */
    public const EXTENDED_FORMAT = 'Y-m-d\TH:i:s.uP';
    public const FORMAT = \DateTime::RFC3339;

    public string $name = 'DateTime';

    public ?string $description = 'A RFC3339 (ISO8601 compliant) formatted date';

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

        $date = \DateTime::createFromFormat(self::EXTENDED_FORMAT, $value);

        if (false === $date) {
            // Fallback on non-extended format::
            if (false === $date = \DateTime::createFromFormat(self::FORMAT, $value)) {
                throw new InvariantViolation('Unable to parse value.');
            }
        }

        // Shift timezone to server's one:
        $date->setTimezone($this->getServerTimezone());

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
        return ['DateTime'];
    }

    private function getServerTimezone(): \DateTimeZone
    {
        return new \DateTimeZone(date_default_timezone_get());
    }
}
