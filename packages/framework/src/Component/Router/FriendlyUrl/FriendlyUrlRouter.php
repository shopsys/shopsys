<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlRouteNotFoundException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class FriendlyUrlRouter implements RouterInterface
{
    /**
     * @var \Symfony\Component\Routing\RouteCollection|null
     */
    protected ?RouteCollection $collection = null;

    /**
     * @param \Symfony\Component\Routing\RequestContext $context
     * @param \Symfony\Component\Config\Loader\LoaderInterface $configLoader
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlGenerator $friendlyUrlGenerator
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlMatcher $friendlyUrlMatcher
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string $friendlyUrlRouterResourceFilepath
     */
    public function __construct(
        protected RequestContext $context,
        protected readonly LoaderInterface $configLoader,
        protected readonly FriendlyUrlGenerator $friendlyUrlGenerator,
        protected readonly FriendlyUrlMatcher $friendlyUrlMatcher,
        protected readonly DomainConfig $domainConfig,
        protected readonly string $friendlyUrlRouterResourceFilepath,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getContext(): RequestContext
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context): void
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection(): RouteCollection
    {
        if ($this->collection === null) {
            $this->collection = $this->configLoader->load($this->friendlyUrlRouterResourceFilepath);
        }

        return $this->collection;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $routeName, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        return $this->friendlyUrlGenerator->generateFromRouteCollection(
            $this->getRouteCollection(),
            $this->domainConfig,
            $routeName,
            $parameters,
            $referenceType
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl $friendlyUrl
     * @param array $parameters
     * @param int $referenceType
     * @return string
     */
    public function generateByFriendlyUrl(FriendlyUrl $friendlyUrl, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        $routeName = $friendlyUrl->getRouteName();
        $route = $this->getRouteCollection()->get($routeName);

        if ($route === null) {
            throw new FriendlyUrlRouteNotFoundException(
                $routeName,
                $this->friendlyUrlRouterResourceFilepath
            );
        }

        return $this->friendlyUrlGenerator->getGeneratedUrl(
            $routeName,
            $route,
            $friendlyUrl,
            $parameters,
            $referenceType
        );
    }

    /**
     * {@inheritdoc}
     */
    public function match(string $pathinfo): array
    {
        return $this->friendlyUrlMatcher->match($pathinfo, $this->getRouteCollection(), $this->domainConfig);
    }
}
