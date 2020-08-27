<?php

declare(strict_types=1);

namespace App\Model\Product\Filter;

use App\Model\CategorySeo\ReadyCategorySeoMix;
use Doctrine\Common\Cache\CacheProvider;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;

class ProductFilterCacheFacade
{
    private const PRODUCT_FILTER_CACHE_LIFETIME = 14400; // 4h

    /**
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    private $cacheProvider;

    /**
     * @param \Doctrine\Common\Cache\CacheProvider $cacheProvider
     */
    public function __construct(
        CacheProvider $cacheProvider
    ) {
        $this->cacheProvider = $cacheProvider;
    }

    /**
     * @param \App\Model\Product\Filter\CachedProductFilterConfig $productFilterConfig
     * @param int $categoryId
     * @param int $domainId
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     */
    public function setProductFilterConfigIntoCache(
        CachedProductFilterConfig $productFilterConfig,
        int $categoryId,
        int $domainId,
        ?ReadyCategorySeoMix $readyCategorySeoMix
    ): void {
        $cacheId = $this->getProductFilterConfigCacheId($categoryId, $domainId, $readyCategorySeoMix);
        $this->cacheProvider->save($cacheId, $productFilterConfig, self::PRODUCT_FILTER_CACHE_LIFETIME);
    }

    /**
     * @param int $categoryId
     * @param int $domainId
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return \App\Model\Product\Filter\CachedProductFilterConfig|null
     */
    public function findProductFilterConfigInCache(
        int $categoryId,
        int $domainId,
        ?ReadyCategorySeoMix $readyCategorySeoMix
    ): ?CachedProductFilterConfig {
        $cacheId = $this->getProductFilterConfigCacheId($categoryId, $domainId, $readyCategorySeoMix);
        if ($this->cacheProvider->contains($cacheId)) {
            $productFilterConfig = $this->cacheProvider->fetch($cacheId);
            if ($productFilterConfig !== false) {
                return $productFilterConfig;
            }
        }

        return null;
    }

    /**
     * @param int $categoryId
     * @param int $domainId
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return string
     */
    private function getProductFilterConfigCacheId(
        int $categoryId,
        int $domainId,
        ?ReadyCategorySeoMix $readyCategorySeoMix
    ): string {
        if ($readyCategorySeoMix !== null) {
            return sprintf('ProductFilterConfig_readyCategorySeoMix-%d_domain-%d_', $readyCategorySeoMix->getId(), $domainId);
        }
        return sprintf('ProductFilterConfig_category-%d_domain-%d', $categoryId, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData
     * @param int $categoryId
     * @param int $domainId
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     */
    public function setProductFilterCountDataIntoCache(
        ProductFilterCountData $productFilterCountData,
        int $categoryId,
        int $domainId,
        ?ReadyCategorySeoMix $readyCategorySeoMix
    ): void {
        $cacheId = $this->getCacheId($categoryId, $domainId, $readyCategorySeoMix);
        $this->cacheProvider->save($cacheId, $productFilterCountData, self::PRODUCT_FILTER_CACHE_LIFETIME);
    }

    /**
     * @param int $categoryId
     * @param int $domainId
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData|null
     */
    public function findProductFilterCountDataInCache(
        int $categoryId,
        int $domainId,
        ?ReadyCategorySeoMix $readyCategorySeoMix
    ): ?ProductFilterCountData {
        $cacheId = $this->getCacheId($categoryId, $domainId, $readyCategorySeoMix);
        if ($this->cacheProvider->contains($cacheId)) {
            $productFilterCountData = $this->cacheProvider->fetch($cacheId);
            if ($productFilterCountData !== false) {
                return $productFilterCountData;
            }
        }

        return null;
    }

    /**
     * @param int $categoryId
     * @param int $domainId
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return string
     */
    private function getCacheId(
        int $categoryId,
        int $domainId,
        ?ReadyCategorySeoMix $readyCategorySeoMix
    ): string {
        if ($readyCategorySeoMix !== null) {
            return sprintf('ProductFilterCountData_readyCategorySeoMix-%d_domain-%d_', $readyCategorySeoMix->getId(), $domainId);
        }
        return sprintf('ProductFilterCountData_category-%d_domain-%d', $categoryId, $domainId);
    }
}
