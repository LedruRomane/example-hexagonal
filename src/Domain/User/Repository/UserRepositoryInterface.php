<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\Common\Exception\NotFoundException;
use App\Domain\User\User;
use Symfony\Component\Uid\Ulid;

/**
 * @method User[] findAll()
 */
interface UserRepositoryInterface
{
    /**
     * @throws NotFoundException on no user for given email
     */
    public function getOneByEmail(string $email): User;

    /**
     * @throws NotFoundException on no user for given uid
     */
    public function getOneByUid(Ulid $uid): User;

    public function save(User $user): void;
}
