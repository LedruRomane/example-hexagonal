<?php

declare(strict_types=1);

namespace App\Application\User\Payload;

use App\Domain\User\User;
use App\Infrastructure\Bridge\GraphQL\Type\Input\StringInputType;
use App\Infrastructure\Bridge\Symfony\Validator\Constraint\EntityReferenceDoesNotExist;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

trait UserProfileTrait
{
    public function __construct(
        /**
         * @internal Only for validation purposes, when updating a user (it's the user uid).
         */
        public readonly ?Ulid $uid = null
    ) {
    }

    /** @var string */
    #[GQL\Field(type: StringInputType::NAME)]
    #[GQL\Description('Mandatory')]
    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Type('string'),
        new Assert\Length(max: 255),
        new Assert\Email(),
        new EntityReferenceDoesNotExist(
            User::class,
            identityField: 'email',
            currentObjectComparisonMethod: 'compareFoundReferenceToCurrentObject',
            message: 'The email {{ value }} is already used.',
        ),
    ])]
    public $email;

    /** @var string */
    #[GQL\Field(type: StringInputType::NAME)]
    #[GQL\Description('Mandatory')]
    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Type('string'),
        new Assert\Length(min: 2, max: 255),
    ])]
    public $firstname;

    /** @var string */
    #[GQL\Field(type: StringInputType::NAME)]
    #[GQL\Description('Mandatory')]
    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Type('string'),
        new Assert\Length(min: 2, max: 255),
    ])]
    public $lastname;

    /**
     * Exclude the current object from the EntityReferenceDoesNotExist violations.
     */
    public function compareFoundReferenceToCurrentObject(): callable
    {
        return fn (User $foundUser): bool => $foundUser->getUid()->equals($this->uid);
    }
}
