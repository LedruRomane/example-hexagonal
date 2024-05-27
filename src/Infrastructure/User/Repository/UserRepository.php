<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Repository;

use App\Domain\Common\Exception\NotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Ulid;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getOneByEmail(string $email): User
    {
        $user = $this->findOneBy(['email' => $email]);

        if (!$user instanceof User) {
            throw new NotFoundException(sprintf('No user found for email "%s"', $email));
        }

        return $user;
    }

    public function getOneByUid(Ulid $uid): User
    {
        $user = $this->findOneBy(['uid' => $uid]);

        if (!$user instanceof User) {
            throw new NotFoundException(sprintf('User with UID "%s" not found', $uid));
        }

        return $user;
    }

    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
    }
}
