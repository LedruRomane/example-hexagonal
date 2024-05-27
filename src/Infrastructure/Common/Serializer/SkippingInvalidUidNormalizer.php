<?php

declare(strict_types=1);

namespace App\Infrastructure\Common\Serializer;

use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Uid\Ulid;

/**
 * Alternative to {@link UidNormalizer} that throws on invalid UID format.
 * Instead, if the {@link AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT} flag is set,
 * we skip the {@link NotNormalizableValueException} and return the original value for further validation.
 */
class SkippingInvalidUidNormalizer implements DenormalizerInterface
{
    private UidNormalizer $uidNormalizer;

    public function __construct(array $defaultContext = [])
    {
        $this->uidNormalizer = new UidNormalizer($defaultContext);
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        try {
            return $this->uidNormalizer->denormalize($data, $type, $format, $context);
        } catch (NotNormalizableValueException) {
            // ignore exception and forwards original data for further validation
            return $data;
        }
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        if ($context[AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT] ?? false) {
            return $this->uidNormalizer->supportsDenormalization($data, $type, $format, $context);
        }

        return false;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Ulid::class => __CLASS__ === static::class];
    }
}
