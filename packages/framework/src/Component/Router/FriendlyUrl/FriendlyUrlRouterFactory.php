<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Contracts\Cache\CacheInterface;

class FriendlyUrlRouterFactory
{
    protected string $friendlyUrlRouterResourceFilepath;

    /**
     * @param mixed $friendlyUrlRouterResourceFilepath
     * @param \Symfony\Component\Config\Loader\LoaderInterface $configLoader
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider
     * @param \Symfony\Contracts\Cache\CacheInterface $mainFriendlyUrlSlugCache
     */
    public function __construct(
        $friendlyUrlRouterResourceFilepath,
        protected readonly LoaderInterface $configLoader,
        protected readonly FriendlyUrlRepository $friendlyUrlRepository,
        protected readonly FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider,
        protected readonly CacheInterface $mainFriendlyUrlSlugCache
    ) {
        $this->friendlyUrlRouterResourceFilepath = $friendlyUrlRouterResourceFilepath;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Symfony\Component\Routing\RequestContext $context
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouter
     */
    public function createRouter(DomainConfig $domainConfig, RequestContext $context)
    {
        return new FriendlyUrlRouter(
            $context,
            $this->configLoader,
            new FriendlyUrlGenerator(
                $context,
                $this->friendlyUrlRepository,
                $this->friendlyUrlCacheKeyProvider,
                $this->mainFriendlyUrlSlugCache
            ),
            new FriendlyUrlMatcher($this->friendlyUrlRepository),
            $domainConfig,
            $this->friendlyUrlRouterResourceFilepath
        );
    }
}
