<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

class NamespacedQuery implements AliasedInterface, QueryInterface
{
    /**
     * Allows to namespace a set of queries by creating an empty resolved object used as namespace.
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
            'namespaced' => 'NamespacedQuery',
        ];
    }
}
