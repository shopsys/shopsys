<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RouterExtension extends AbstractExtension
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(protected readonly DomainRouterFactory $domainRouterFactory)
    {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'findUrlByDomainId',
                [$this, 'findUrlByDomainId'],
            ),
        ];
    }

    /**
     * @param string $route
     * @param array $routeParams
     * @param int $domainId
     * @return string|null
     */
    public function findUrlByDomainId($route, array $routeParams, $domainId)
    {
        $domainRouter = $this->domainRouterFactory->getRouter($domainId);

        try {
            return $domainRouter->generate($route, $routeParams, UrlGeneratorInterface::ABSOLUTE_URL);
        } catch (RouteNotFoundException $e) {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'router_extension';
    }
}
