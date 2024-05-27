<?php

declare(strict_types=1);

namespace App\Infrastructure\User\GraphQL\Resolver;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\User;
use App\Infrastructure\Bridge\GraphQL\Resolver\AbstractResolver;
use App\Infrastructure\Security\User\Identity;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Symfony\Component\Uid\Ulid;

class UserResolver extends AbstractResolver implements AliasedInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function me(): ?User
    {
        if (null === $user = $this->getDomainUserOrNull()) {
            return null;
        }

        return $user;
    }

    public function find(Ulid $uid): User
    {
        return $this->withGraphQLErrorHandler(fn () => $this->userRepository->getOneByUid($uid));
    }

    /**
     * @return array<User>
     */
    public function listAll(): array
    {
        return $this->userRepository->findAll();
    }

    /**
     * @return array<string>
     */
    public function roles(User $user): array
    {
        return Identity::roles($user->isAdmin());
    }

    public static function getAliases(): array
    {
        return [
            'me' => 'User.me',
            'find' => 'User.find',
            'listAll' => 'User.list',
            'roles' => 'User.roles',
            'listMemberForExchanges' => 'User.listMemberForExchanges',
        ];
    }
}
