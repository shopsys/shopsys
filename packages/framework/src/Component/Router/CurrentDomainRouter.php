<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Cmf\Component\Routing\ChainRouterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;

class CurrentDomainRouter implements ChainRouterInterface
{
    protected RequestContext $context;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(protected readonly Domain $domain, protected readonly DomainRouterFactory $domainRouterFactory)
    {
    }

    /**
     * @return \Symfony\Component\Routing\RequestContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param \Symfony\Component\Routing\RequestContext $context
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }

    /**
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function getRouteCollection()
    {
        return $this->getDomainRouter()->getRouteCollection();
    }

    /**
     * @param string $routeName
     * @param array $parameters
     * @param int $referenceType
     * @return string
     */
    public function generate($routeName, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        return $this->getDomainRouter()->generate($routeName, $parameters, $referenceType);
    }

    /**
     * @param string $pathinfo
     * @return array
     */
    public function match($pathinfo)
    {
        return $this->getDomainRouter()->match($pathinfo);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\DomainRouter
     */
    protected function getDomainRouter()
    {
        return $this->domainRouterFactory->getRouter($this->domain->getId());
    }

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param int $priority
     */
    public function add($router, $priority = 0)
    {
        $this->getDomainRouter()->add($router, $priority);
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface[]
     */
    public function all()
    {
        return $this->getDomainRouter()->all();
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     */
    public function matchRequest(Request $request)
    {
        return $this->getDomainRouter()->matchRequest($request);
    }
}
