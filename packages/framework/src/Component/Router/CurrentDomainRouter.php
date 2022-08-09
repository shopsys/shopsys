<?php

namespace Shopsys\FrameworkBundle\Component\Router;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Cmf\Component\Routing\ChainRouterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class CurrentDomainRouter implements ChainRouterInterface
{
    /**
     * @var \Symfony\Component\Routing\RequestContext
     */
    protected $context;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory
     */
    protected $domainRouterFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(Domain $domain, DomainRouterFactory $domainRouterFactory)
    {
        $this->domain = $domain;
        $this->domainRouterFactory = $domainRouterFactory;
    }

    /**
     * @return \Symfony\Component\Routing\RequestContext
     */
    public function getContext(): RequestContext
    {
        return $this->context;
    }

    /**
     * @param \Symfony\Component\Routing\RequestContext $context
     */
    public function setContext(RequestContext $context): void
    {
        $this->context = $context;
    }

    /**
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function getRouteCollection(): RouteCollection
    {
        return $this->getDomainRouter()->getRouteCollection();
    }

    /**
     * @param string $routeName
     * @param array<string, mixed> $parameters
     * @param int $referenceType
     * @return string
     */
    public function generate($routeName, $parameters = [], $referenceType = self::ABSOLUTE_PATH): string
    {
        return $this->getDomainRouter()->generate($routeName, $parameters, $referenceType);
    }

    /**
     * @param string $pathinfo
     * @return array<string, mixed>
     */
    public function match($pathinfo): array
    {
        return $this->getDomainRouter()->match($pathinfo);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\DomainRouter
     */
    protected function getDomainRouter(): DomainRouter
    {
        return $this->domainRouterFactory->getRouter($this->domain->getId());
    }

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param int $priority
     */
    public function add($router, $priority = 0): void
    {
        $this->getDomainRouter()->add($router, $priority);
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface[]
     */
    public function all(): array
    {
        return $this->getDomainRouter()->all();
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array<string, mixed>
     */
    public function matchRequest(Request $request): array
    {
        return $this->getDomainRouter()->matchRequest($request);
    }
}
