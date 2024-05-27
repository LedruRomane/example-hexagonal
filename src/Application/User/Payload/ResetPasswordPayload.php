<?php

declare(strict_types=1);

namespace App\Application\User\Payload;

use App\Infrastructure\Bridge\GraphQL\Type\Input\StringInputType;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\PasswordStrength;

#[GQL\Input(name: 'ResetPasswordPayload')]
final class ResetPasswordPayload
{
    /** @var string */
    #[GQL\Field(type: StringInputType::NAME)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\PasswordStrength(minScore: PasswordStrength::STRENGTH_MEDIUM)]
    public $newPassword;

    /** @var string */
    #[GQL\Field(type: StringInputType::NAME)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\IdenticalTo(propertyPath: 'newPassword', message: 'The passwords must be identical.')]
    public $newPasswordConfirm;
}
