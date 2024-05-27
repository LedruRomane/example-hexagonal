<?php

declare(strict_types=1);

namespace App\Application\User\Payload;

use App\Infrastructure\Bridge\GraphQL\Type\Input\BooleanInputType;
use App\Infrastructure\Bridge\GraphQL\Type\Input\StringInputType;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\PasswordStrength;

#[GQL\Input(name: 'UserPayload')]
final class UserPayload
{
    use UserProfileTrait;

    /** @var string|null */
    #[GQL\Field(type: StringInputType::NAME)]
    #[Assert\Sequentially([
        new Assert\Type('string'),
        new Assert\NotBlank(allowNull: true),
        new Assert\PasswordStrength(minScore: PasswordStrength::STRENGTH_MEDIUM),
    ])]
    public $password;

    /** @var bool */
    #[GQL\Field(type: BooleanInputType::NAME)]
    #[GQL\Description('Mandatory')]
    #[Assert\Sequentially([
        new Assert\NotNull(),
        new Assert\Type('bool'),
    ])]
    public $active = false;

    /** @var bool */
    #[GQL\Field(type: BooleanInputType::NAME)]
    #[GQL\Description('Mandatory')]
    #[Assert\Sequentially([
        new Assert\NotNull(),
        new Assert\Type('bool'),
    ])]
    public $admin = false;
}
