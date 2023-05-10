<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use App\Model\CategorySeo\ReadyCategorySeoMixRepository;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlCacheKeyProvider;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlGenerator;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouter;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouterFactory as BaseFriendlyUrlRouterFactory;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @property \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
 */
class FriendlyUrlRouterFactory extends BaseFriendlyUrlRouterFactory
{
    /**
     * @param mixed $friendlyUrlRouterResourceFilepath
     * @param \Symfony\Component\Config\Loader\LoaderInterface $configLoader
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider
     * @param \Symfony\Contracts\Cache\CacheInterface $mainFriendlyUrlSlugCache
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixRepository $readyCategorySeoMixRepository
     */
    public function __construct(
        $friendlyUrlRouterResourceFilepath,
        LoaderInterface $configLoader,
        FriendlyUrlRepository $friendlyUrlRepository,
        FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider,
        CacheInterface $mainFriendlyUrlSlugCache,
        private readonly ReadyCategorySeoMixRepository $readyCategorySeoMixRepository,
    ) {
        parent::__construct(
            $friendlyUrlRouterResourceFilepath,
            $configLoader,
            $friendlyUrlRepository,
            $friendlyUrlCacheKeyProvider,
            $mainFriendlyUrlSlugCache
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Symfony\Component\Routing\RequestContext $context
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouter
     */
    public function createRouter(DomainConfig $domainConfig, RequestContext $context): FriendlyUrlRouter
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
            new FriendlyUrlMatcher($this->friendlyUrlRepository, $this->readyCategorySeoMixRepository),
            $domainConfig,
            $this->friendlyUrlRouterResourceFilepath
        );
    }
}
