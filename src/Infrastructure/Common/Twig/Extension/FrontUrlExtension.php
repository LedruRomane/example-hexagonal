<?php

declare(strict_types=1);

namespace App\Infrastructure\Common\Twig\Extension;

use App\Infrastructure\Common\Router\FrontUrlGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FrontUrlExtension extends AbstractExtension
{
    public function __construct(
        private readonly FrontUrlGenerator $generator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('front_url', $this->getUrl(...)),
        ];
    }

    public function getUrl(string $name, array $parameters = []): string
    {
        return $this->generator->generate($name, $parameters);
    }
}
