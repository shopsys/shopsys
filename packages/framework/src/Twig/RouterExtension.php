<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RouterExtension extends AbstractExtension
{
    public function __construct(protected readonly DomainRouterFactory $domainRouterFactory)
    {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'findUrlByDomainId',
                $this->findUrlByDomainId(...),
            ),
        ];
    }

    public function findUrlByDomainId(string $route, array $routeParams, int $domainId): ?string
    {
        $domainRouter = $this->domainRouterFactory->getRouter($domainId);

        try {
            return $domainRouter->generate($route, $routeParams, UrlGeneratorInterface::ABSOLUTE_URL);
        } catch (RouteNotFoundException $e) {
            return null;
        }
    }

    public function getName(): string
    {
        return 'router_extension';
    }
}
