<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\Symfony\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Used for GraphQL mutations to denormalize payload, but avoiding to double denormalize already instantiated objects.
 */
class SkippingInstantiatedObjectDenormalizer implements DenormalizerInterface
{
    public const SKIP = 'skip_instantiated_object';

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return ($context[self::SKIP] ?? false) && \is_object($data);
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['object' => __CLASS__ === static::class];
    }
}
