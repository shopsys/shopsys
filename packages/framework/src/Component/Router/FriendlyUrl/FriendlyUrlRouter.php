<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class FriendlyUrlRouter implements RouterInterface
{
    /**
     * @var \Symfony\Component\Routing\RequestContext
     */
    private $context;

    /**
     * @var \Symfony\Component\Config\Loader\LoaderInterface
     */
    private $configLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlGenerator
     */
    private $friendlyUrlGenerator;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlMatcher
     */
    private $friendlyUrlMatcher;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    private $domainConfig;

    /**
     * @var string
     */
    private $friendlyUrlRouterResourceFilepath;

    /**
     * @var \Symfony\Component\Routing\RouteCollection
     */
    private $collection;
    
    public function __construct(
        RequestContext $context,
        LoaderInterface $configLoader,
        FriendlyUrlGenerator $friendlyUrlGenerator,
        FriendlyUrlMatcher $friendlyUrlMatcher,
        DomainConfig $domainConfig,
        string $friendlyUrlRouterResourceFilepath
    ) {
        $this->context = $context;
        $this->configLoader = $configLoader;
        $this->friendlyUrlGenerator = $friendlyUrlGenerator;
        $this->friendlyUrlMatcher = $friendlyUrlMatcher;
        $this->domainConfig = $domainConfig;
        $this->friendlyUrlRouterResourceFilepath = $friendlyUrlRouterResourceFilepath;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {
        if ($this->collection === null) {
            $this->collection = $this->configLoader->load($this->friendlyUrlRouterResourceFilepath);
        }

        return $this->collection;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($routeName, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        return $this->friendlyUrlGenerator->generateFromRouteCollection(
            $this->getRouteCollection(),
            $this->domainConfig,
            $routeName,
            $parameters,
            $referenceType
        );
    }
    
    public function generateByFriendlyUrl(FriendlyUrl $friendlyUrl, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        $routeName = $friendlyUrl->getRouteName();
        $route = $this->getRouteCollection()->get($routeName);

        if ($route === null) {
            throw new \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlRouteNotFoundException(
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
    public function match($pathinfo)
    {
        return $this->friendlyUrlMatcher->match($pathinfo, $this->getRouteCollection(), $this->domainConfig);
    }
}
