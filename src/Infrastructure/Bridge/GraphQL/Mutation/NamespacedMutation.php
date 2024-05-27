<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Mutation;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class NamespacedMutation implements AliasedInterface, MutationInterface
{
    /**
     * Allows to namespace a set of mutations by creating an empty resolved object used as namespace.
     *
     * @see https://graphql-rules.com/rules/mutation-namespaces
     */
    public function namespaced(): object
    {
        return new \stdClass();
    }

    public static function getAliases(): array
    {
        return [
            'namespaced' => 'NamespacedMutation',
        ];
    }
}
