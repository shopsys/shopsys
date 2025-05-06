<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Symfony\Cmf\Component\Routing\ChainRouterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class CurrentDomainRouter implements ChainRouterInterface
{
    protected RequestContext $context;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\AdministrationRouter $administrationRouter
     * @param \Shopsys\FrameworkBundle\Component\String\TransformStringHelper $transformStringHelper
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly AdministrationRouter $administrationRouter,
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
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
     * @param string $name
     * @param array $parameters
     * @param int $referenceType
     * @return string
     */
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        if ($referenceType === self::ABSOLUTE_PATH) {
            $url = $this->getDomainRouter()->generate($name, $parameters, $referenceType);

            $domainPostfix = $this->domain->getPostfix();

            if ($domainPostfix !== null) {
                return $this->transformStringHelper->removeStringFromStart($url, $domainPostfix);
            }
        }

        return $this->getDomainRouter()->generate($name, $parameters, $referenceType);
    }

    /**
     * @param string $pathinfo
     * @return array
     */
    public function match(string $pathinfo): array
    {
        return $this->getDomainRouter()->match($pathinfo);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\DomainRouter|\Shopsys\FrameworkBundle\Component\Router\AdministrationRouter
     */
    protected function getDomainRouter(): RouterInterface
    {
        if ($this->domain->isDomainResolvedByFallback()) {
            return $this->administrationRouter;
        }

        return $this->domainRouterFactory->getRouter($this->domain->getId());
    }

    /**
     * @param \Symfony\Component\Routing\RouterInterface|\Symfony\Component\Routing\Matcher\RequestMatcherInterface|\Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
     * @param int $priority
     */
    public function add(RouterInterface|RequestMatcherInterface|UrlGeneratorInterface $router, int $priority = 0): void
    {
        $this->getDomainRouter()->add($router, $priority);
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface[]
     */
    public function all(): array
    {
        /** @var \Symfony\Component\Routing\RouterInterface[] $allRouters */
        $allRouters = $this->getDomainRouter()->all();

        return $allRouters;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     */
    public function matchRequest(Request $request): array
    {
        return $this->getDomainRouter()->matchRequest($request);
    }
}
