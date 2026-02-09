<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CsrfExtension extends AbstractExtension
{
    protected UrlGeneratorInterface $router;

    public function __construct(
        protected RouteCsrfProtector $routeCsrfProtector,
        UrlGeneratorInterface $generator,
    ) {
        $this->router = $generator;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('protectedUrl', $this->protectedUrl(...)),
        ];
    }

    public function protectedUrl(string $name, array $parameters = [], bool $schemeRelative = false): string
    {
        $parameters[RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER] = $this->routeCsrfProtector->getCsrfTokenByRoute(
            $name,
        );

        return $this->router->generate($name, $parameters, $schemeRelative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
