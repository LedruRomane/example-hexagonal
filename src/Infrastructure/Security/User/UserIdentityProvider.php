<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\User;

use App\Domain\Common\Exception\NotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\User;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserIdentityProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly ManagerRegistry $doctrine,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        try {
            // Fetch the domain user from database, if it exists:
            $user = $this->userRepository->getOneByEmail($identifier);

            // Return the identity object that is stored in Symfony's security system and
            // allow us to make the link with our model:
            return new Identity($user);
        } catch (NotFoundException $ex) {
            throw new UserNotFoundException($identifier, $ex->getCode(), $ex);
        }
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof Identity) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', \get_class($user)));
        }

        // Same logic for now, since we don't have any particular constraints for checking a user is still fresh:
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        \assert($user instanceof Identity);
        $user->setPassword($newHashedPassword);

        /** @var ObjectManager $em */
        $em = $this->doctrine->getManagerForClass(User::class);
        $em->flush();
    }

    public function supportsClass(string $class): bool
    {
        return $class === Identity::class;
    }
}
