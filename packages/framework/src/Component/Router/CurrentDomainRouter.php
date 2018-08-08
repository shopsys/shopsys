<?php

namespace Shopsys\FrameworkBundle\Component\Router;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class CurrentDomainRouter implements RouterInterface
{
    /**
     * @var \Symfony\Component\Routing\RequestContext
     */
    private $context;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory
     */
    private $domainRouterFactory;

    public function __construct(Domain $domain, DomainRouterFactory $domainRouterFactory)
    {
        $this->domain = $domain;
        $this->domainRouterFactory = $domainRouterFactory;
    }

    public function getContext(): \Symfony\Component\Routing\RequestContext
    {
        return $this->context;
    }

    public function setContext(RequestContext $context): void
    {
        $this->context = $context;
    }

    public function getRouteCollection(): \Symfony\Component\Routing\RouteCollection
    {
        return $this->getDomainRouter()->getRouteCollection();
    }
    
    public function generate(string $routeName, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        return $this->getDomainRouter()->generate($routeName, $parameters, $referenceType);
    }
    
    public function match(string $pathinfo): array
    {
        return $this->getDomainRouter()->match($pathinfo);
    }

    private function getDomainRouter(): \Shopsys\FrameworkBundle\Component\Router\DomainRouter
    {
        return $this->domainRouterFactory->getRouter($this->domain->getId());
    }
}
