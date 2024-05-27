<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\Symfony\Validator\Constraint;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

/**
 * Check an entity reference exists according to given identifier field.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class EntityReferenceDoesNotExist extends Constraint
{
    public const REF_DOES_NOT_EXIST = 'ca2aafec-1542-4b55-8e5f-128e88e9f68a';

    /** @var array<string,string> */
    protected const ERROR_NAMES = [
        self::REF_DOES_NOT_EXIST => 'REF_DOES_NOT_EXIST',
    ];

    #[HasNamedArguments]
    public function __construct(
        /** @var class-string The entity class */
        public string $entity,
        public string $identityField = 'uid',
        /**
         * A propertyPath to the current object to exclude from comparison.
         * E.g: when updating an entity, whenever the current object being updated is the same
         * as the one found by the reference ($identityField), do not consider this as a violation.
         */
        public ?string $currentObjectPath = null,
        /**
         * Rather than comparing with the injected current object (@see EntityReferenceDoesNotExist::$currentObjectPath),
         * use a custom comparison method.
         */
        public ?string $currentObjectComparisonMethod = null,
        public ?string $repositoryMethod = null,
        public string $message = 'The {{ value }} reference exists.',
        public ?string $code = null,
        array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }
}
