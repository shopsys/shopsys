<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class BrandCachedFacade
{
    protected const string BRAND_URL_CACHE_NAMESPACE = 'brandUrl';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache
     */
    public function __construct(
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    /**
     * @param int $brandId
     * @param int $domainId
     * @return string
     */
    public function getBrandUrlByDomainId(int $brandId, int $domainId): string
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::BRAND_URL_CACHE_NAMESPACE,
            fn () => $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId(
                $domainId,
                'front_brand_detail',
                $brandId,
            ),
            $brandId,
            $domainId,
        );
    }
}
