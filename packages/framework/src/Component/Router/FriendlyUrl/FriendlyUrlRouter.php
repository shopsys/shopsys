<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlRouteNotFoundException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class FriendlyUrlRouter implements RouterInterface
{
    public const string ROUTE_OPTION_MULTIDOMAIN = 'multidomain';

    protected ?RouteCollection $collection = null;

    public function __construct(
        protected RequestContext $context,
        protected readonly LoaderInterface $configLoader,
        protected readonly FriendlyUrlGenerator $friendlyUrlGenerator,
        protected readonly FriendlyUrlMatcher $friendlyUrlMatcher,
        protected readonly DomainConfig $domainConfig,
        protected readonly string $friendlyUrlRouterResourceFilepath,
    ) {
    }

    #[Override]
    public function getContext(): RequestContext
    {
        return $this->context;
    }

    #[Override]
    public function setContext(RequestContext $context): void
    {
        $this->context = $context;
    }

    #[Override]
    public function getRouteCollection(): RouteCollection
    {
        if ($this->collection === null) {
            $this->collection = $this->configLoader->load($this->friendlyUrlRouterResourceFilepath, 'php');
        }

        return $this->collection;
    }

    #[Override]
    public function generate(
        string $routeName,
        array $parameters = [],
        int $referenceType = self::ABSOLUTE_PATH,
    ): string {
        return $this->friendlyUrlGenerator->generateFromRouteCollection(
            $this->getRouteCollection(),
            $this->domainConfig,
            $routeName,
            $parameters,
            $referenceType,
        );
    }

    public function generateByFriendlyUrl(
        FriendlyUrl $friendlyUrl,
        array $parameters = [],
        int $referenceType = self::ABSOLUTE_PATH,
    ): string {
        $routeName = $friendlyUrl->getRouteName();
        $route = $this->getRouteCollection()->get($routeName);

        if ($route === null) {
            throw new FriendlyUrlRouteNotFoundException(
                $routeName,
                $this->friendlyUrlRouterResourceFilepath,
            );
        }

        return $this->friendlyUrlGenerator->getGeneratedUrl(
            $routeName,
            $route,
            $friendlyUrl,
            $parameters,
            $referenceType,
        );
    }

    #[Override]
    public function match(string $pathinfo): array
    {
        return $this->friendlyUrlMatcher->match($pathinfo, $this->getRouteCollection(), $this->domainConfig);
    }
}
