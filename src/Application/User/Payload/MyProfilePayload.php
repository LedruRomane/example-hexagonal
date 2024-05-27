<?php

declare(strict_types=1);

namespace App\Application\User\Payload;

use Overblog\GraphQLBundle\Annotation as GQL;

#[GQL\Input(name: 'MyProfilePayload')]
final class MyProfilePayload
{
    use UserProfileTrait;
}
