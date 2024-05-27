<?php

declare(strict_types=1);

namespace App\Infrastructure\User\GraphQL\Mutation;

use App\Application\User\Command\CreateUserCommand;
use App\Application\User\Command\ForgotPasswordCommand;
use App\Application\User\Command\ResetPasswordCommand;
use App\Application\User\Command\UpdateMyProfileCommand;
use App\Application\User\Command\UpdateUserCommand;
use App\Application\User\Payload\MyProfilePayload;
use App\Application\User\Payload\ResetPasswordPayload;
use App\Application\User\Payload\UserPayload;
use App\Domain\User\User;
use App\Infrastructure\Bridge\GraphQL\Error\CustomUserError;
use App\Infrastructure\Bridge\GraphQL\Mutation\AbstractMutation;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Uid\Ulid;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

class UserMutation extends AbstractMutation implements AliasedInterface
{
    public function create(Argument $args): User
    {
        $payload = $this->getPayload($args, UserPayload::class);

        /** @var User $user */
        $user = $this->handle(new CreateUserCommand($payload));

        return $user;
    }

    public function update(Ulid $uid, Argument $args): User
    {
        $payload = $this->getPayload($args, UserPayload::class, [
            ObjectNormalizer::OBJECT_TO_POPULATE => new UserPayload($uid),
        ]);

        /** @var User $user */
        $user = $this->handle(new UpdateUserCommand($uid, $payload));

        return $user;
    }

    public function updateMyProfile(Argument $args): User
    {
        $payload = $this->getPayload($args, MyProfilePayload::class, [
            ObjectNormalizer::OBJECT_TO_POPULATE => new MyProfilePayload($this->getDomainUser()->getUid()),
        ]);

        /** @var User $user */
        $user = $this->handle(new UpdateMyProfileCommand($this->getDomainUser(), $payload));

        return $user;
    }

    public function forgotPassword(string $email): bool
    {
        $this->handle(new ForgotPasswordCommand($email));

        return true;
    }

    public function resetPassword(string $token, Argument $args): bool
    {
        $payload = $this->getPayload($args, ResetPasswordPayload::class);

        try {
            $this->handle(new ResetPasswordCommand($token, $payload));
        } catch (ResetPasswordExceptionInterface $ex) {
            throw new CustomUserError(new TranslatableMessage(
                $ex->getReason(),
                [],
                'ResetPasswordBundle'
            ), $ex);
        }

        return true;
    }

    public static function getAliases(): array
    {
        return [
            'create' => 'User.create',
            'update' => 'User.update',
            'updateMyProfile' => 'User.updateMyProfile',
            'forgotPassword' => 'User.forgotPassword',
            'resetPassword' => 'User.resetPassword',
        ];
    }
}
