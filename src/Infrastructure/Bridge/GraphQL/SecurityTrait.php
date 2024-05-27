<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL;

use App\Domain\User\User;
use App\Infrastructure\Bridge\GraphQL\Error\ForbiddenError;
use App\Infrastructure\Security\User\Identity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

trait SecurityTrait
{
    abstract protected function getSecurity(): Security;

    protected function denyAccessUnlessGranted(mixed $subject, string|array $attributes): void
    {
        foreach ((array) $attributes as $attribute) {
            if ($this->getSecurity()->isGranted($attribute, $subject)) {
                return;
            }
        }

        $accessDenied = new AccessDeniedException('Access Denied.');
        $accessDenied->setAttributes($attributes);
        $accessDenied->setSubject($subject);

        throw new ForbiddenError($accessDenied->getMessage(), $accessDenied);
    }

    protected function getUserIdentity(): ?Identity
    {
        $identity = $this->getSecurity()->getUser();

        if (null === $identity) {
            return null;
        }

        if (!$identity instanceof Identity) {
            throw new \LogicException(sprintf(
                'Expected an instance of "%s". Got "%s"',
                Identity::class,
                get_debug_type($identity),
            ));
        }

        return $identity;
    }

    protected function getDomainUserOrNull(): ?User
    {
        if (null === $identity = $this->getUserIdentity()) {
            return null;
        }

        return $identity->getUser();
    }

    protected function getDomainUser(): User
    {
        $user = $this->getDomainUserOrNull();

        if (null === $user) {
            throw new \LogicException('User is not available.');
        }

        return $user;
    }
}
