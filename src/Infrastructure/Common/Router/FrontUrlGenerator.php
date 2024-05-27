<?php

declare(strict_types=1);

namespace App\Infrastructure\Common\Router;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * URL generator for the front app, allowing to use the Symfony route config to generate these.
 * However, we replace the request context for such routes, since everything must be provided in the configuration
 * instead, and the port must not be overridden if provided in the APP_FRONT_HOST env var.
 */
class FrontUrlGenerator
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function generate(
        string $route,
        array $parameters,
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_URL
    ): string {
        $prevContext = $this->urlGenerator->getContext();

        try {
            // use an empty request context since the route must provide everything:
            $this->urlGenerator->setContext(new RequestContext());

            return $this->urlGenerator->generate($route, $parameters, $referenceType);
        } finally {
            $this->urlGenerator->setContext($prevContext);
        }
    }
}
