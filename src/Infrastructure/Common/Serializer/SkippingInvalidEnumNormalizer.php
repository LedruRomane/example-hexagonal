<?php

declare(strict_types=1);

namespace App\Infrastructure\Common\Serializer;

use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Same as {@see SkippingInvalidUidNormalizer} for enums
 */
class SkippingInvalidEnumNormalizer implements DenormalizerInterface
{
    private BackedEnumNormalizer $internal;

    public function __construct()
    {
        $this->internal = new BackedEnumNormalizer();
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        try {
            return $this->internal->denormalize($data, $type, $format, $context);
        } catch (NotNormalizableValueException $e) {
            // ignore exception and forwards original data for further validation
            return $data;
        }
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        if ($context[AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT] ?? false) {
            return $this->internal->supportsDenormalization($data, $type, $format, $context);
        }

        return false;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [\BackedEnum::class => __CLASS__ === static::class];
    }
}
