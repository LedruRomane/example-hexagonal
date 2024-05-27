<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\Symfony\Validator\Constraint;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
class EntityReferenceExists extends Constraint
{
    public const REF_DOES_NOT_EXIST = 'd3ead236-bee1-4e1d-ad41-2b2d7c303dc3';

    protected const ERROR_NAMES = [
        self::REF_DOES_NOT_EXIST => 'REF_DOES_NOT_EXIST',
    ];

    #[HasNamedArguments]
    public function __construct(
        /** @var class-string The entity class */
        public string $entity,
        public string $identityField = 'uid',
        public string $message = 'The {{ value }} reference exists.',
        array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }
}
