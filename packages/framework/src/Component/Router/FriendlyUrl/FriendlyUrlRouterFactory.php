<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixRepository;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Contracts\Cache\CacheInterface;

class FriendlyUrlRouterFactory
{
    public function __construct(
        protected string $friendlyUrlRouterResourceFilepath,
        protected readonly LoaderInterface $configLoader,
        protected readonly FriendlyUrlRepository $friendlyUrlRepository,
        protected readonly FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider,
        protected readonly CacheInterface $mainFriendlyUrlSlugCache,
        protected readonly ReadyCategorySeoMixRepository $readyCategorySeoMixRepository,
    ) {
    }

    public function createRouter(DomainConfig $domainConfig, RequestContext $context): FriendlyUrlRouter
    {
        return new FriendlyUrlRouter(
            $context,
            $this->configLoader,
            new FriendlyUrlGenerator(
                $context,
                $this->friendlyUrlRepository,
                $this->friendlyUrlCacheKeyProvider,
                $this->mainFriendlyUrlSlugCache,
            ),
            new FriendlyUrlMatcher($this->friendlyUrlRepository, $this->readyCategorySeoMixRepository),
            $domainConfig,
            $this->friendlyUrlRouterResourceFilepath,
        );
    }
}
