<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Mutation;

use App\Application\Bus\CommandBusInterface;
use App\Domain\Common\Exception\ForbiddenException;
use App\Domain\Common\Exception\NotFoundException;
use App\Infrastructure\Bridge\GraphQL\Error\ForbiddenError;
use App\Infrastructure\Bridge\GraphQL\Error\InvalidPayloadError;
use App\Infrastructure\Bridge\GraphQL\Error\NotFoundError;
use App\Infrastructure\Bridge\GraphQL\SecurityTrait;
use App\Infrastructure\Bridge\Symfony\DependencyInjection\ServiceLocatorTrait;
use App\Infrastructure\Bridge\Symfony\Serializer\Normalizer\SkippingInstantiatedObjectDenormalizer;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractMutation implements MutationInterface, ServiceSubscriberInterface
{
    use ServiceLocatorTrait;
    use SecurityTrait;

    /**
     * @phpstan-return mixed
     */
    protected function handle(object $command)
    {
        try {
            return $this->get(CommandBusInterface::class)->handle($command);
        } catch (\Throwable $ex) {
            try {
                if ($ex instanceof HandlerFailedException) {
                    $ex = $ex->getNestedExceptions()[0];
                }

                throw $ex;
            } catch (ValidationFailedException $ex) {
                throw new InvalidPayloadError($ex->getViolations());
            } catch (NotFoundException $ex) {
                throw new NotFoundError($ex->getMessage(), $ex);
            } catch (ForbiddenException $ex) {
                throw new ForbiddenError($ex->getMessage(), $ex);
            }
        }
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $payloadClass
     *
     * @phpstan-return T
     */
    protected function getPayload(Argument $data, string $payloadClass, array $context = [])
    {
        /** @var array $payload */
        $payload = $data['payload'];

        return $this->denormalize($payload, $payloadClass, $context);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @phpstan-return T
     */
    protected function denormalize(array $data, string $class, array $context = [])
    {
        return $this->get(DenormalizerInterface::class)->denormalize($data, $class, null, $context + [
            SkippingInstantiatedObjectDenormalizer::SKIP => true,
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
        ]);
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->get(LoggerInterface::class);
    }

    protected function getSecurity(): Security
    {
        return $this->get(Security::class);
    }

    public static function getSubscribedServices(): array
    {
        return [
            CommandBusInterface::class,
            DenormalizerInterface::class,
            Security::class,
            TranslatorInterface::class,
            ValidatorInterface::class,
            LoggerInterface::class,
        ];
    }
}
