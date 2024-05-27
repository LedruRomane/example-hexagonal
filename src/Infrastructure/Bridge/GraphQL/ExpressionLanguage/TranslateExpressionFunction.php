<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\ExpressionLanguage;

use Overblog\GraphQLBundle\ExpressionLanguage\ExpressionFunction;
use Overblog\GraphQLBundle\Generator\TypeGenerator;

/**
 * Registers an extension to the expression language used by the GraphQL bundle,
 * so we can translate values directly from the config.
 *
 * @see https://github.com/overblog/GraphQLBundle/blob/0.15/docs/definitions/expression-language.md#custom-expression-functions
 */
class TranslateExpressionFunction extends ExpressionFunction
{
    public function __construct()
    {
        parent::__construct(
            'trans',
            fn (string $id) => "$this->gqlServices->get('container')->get('translator')->trans($id)",
            static fn (array $arguments, string $id) => $arguments[TypeGenerator::GRAPHQL_SERVICES]->get(
                'container'
            )->get('translator')->translate($id)
        );
    }
}
