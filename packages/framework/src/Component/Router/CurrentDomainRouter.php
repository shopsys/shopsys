<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlMatcher;
use Symfony\Cmf\Component\Routing\ChainRouterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
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
     * @param \Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface $contextResolver
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly ContextResolverInterface $contextResolver,
    ) {
    }

    /**
     * @return \Symfony\Component\Routing\RequestContext
     */
    #[Override]
    public function getContext(): RequestContext
    {
        return $this->context;
    }

    /**
     * @param \Symfony\Component\Routing\RequestContext $context
     */
    #[Override]
    public function setContext(RequestContext $context): void
    {
        $this->context = $context;
    }

    /**
     * @return \Symfony\Component\Routing\RouteCollection
     */
    #[Override]
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
    #[Override]
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        return $this->getDomainRouter()->generate($name, $parameters, $referenceType);
    }

    /**
     * @param string $pathinfo
     * @return array
     */
    #[Override]
    public function match(string $pathinfo): array
    {
        return $this->getDomainRouter()->match($pathinfo);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\DomainRouter
     */
    protected function getDomainRouter(): RouterInterface
    {
        return $this->domainRouterFactory->getRouter($this->domain->getId());
    }

    /**
     * @param \Symfony\Component\Routing\RouterInterface|\Symfony\Component\Routing\Matcher\RequestMatcherInterface|\Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
     * @param int $priority
     */
    #[Override]
    public function add(RouterInterface|RequestMatcherInterface|UrlGeneratorInterface $router, int $priority = 0): void
    {
        $this->getDomainRouter()->add($router, $priority);
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface[]
     */
    #[Override]
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
    #[Override]
    public function matchRequest(Request $request): array
    {
        if ($this->contextResolver->isCurrentContext(AdminContext::class) || str_starts_with($request->getPathInfo(), FriendlyUrlMatcher::IGNORED_INTERNAL_ROUTE)) {
            throw new ResourceNotFoundException();
        }

        return $this->getDomainRouter()->matchRequest($request);
    }
}
