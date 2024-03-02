<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Collection;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Image\ImageUrlWithSizeHelper;
use Shopsys\FrameworkBundle\Model\Product\Collection\Exception\ProductImageUrlNotLoadedException;
use Shopsys\FrameworkBundle\Model\Product\Collection\Exception\ProductUrlNotLoadedException;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductUrlsBatchLoader
{
    protected const string PRODUCT_URLS_CACHE_NAMESPACE = 'loadedProductUrls';
    protected const string PRODUCT_IMAGE_URLS_CACHE_NAMESPACE = 'loadedProductImageUrls';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade $productCollectionFacade
     * @param \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache
     */
    public function __construct(
        protected readonly ProductCollectionFacade $productCollectionFacade,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     */
    public function loadForProducts(array $products, DomainConfig $domainConfig): void
    {
        $productUrlsById = $this->productCollectionFacade->getAbsoluteUrlsIndexedByProductId($products, $domainConfig);
        $productImageUrlsById = $this->productCollectionFacade->getImagesUrlsIndexedByProductId(
            $products,
            $domainConfig,
        );

        foreach ($products as $product) {
            $key = $this->getKey($product, $domainConfig);
            $productId = $product->getId();

            $this->inMemoryCache->save(static::PRODUCT_URLS_CACHE_NAMESPACE, $productUrlsById[$productId], $key);
            $this->inMemoryCache->save(static::PRODUCT_IMAGE_URLS_CACHE_NAMESPACE, $productImageUrlsById[$productId], $key);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string
     */
    public function getProductUrl(Product $product, DomainConfig $domainConfig): string
    {
        $key = $this->getKey($product, $domainConfig);

        if ($this->inMemoryCache->hasItem(static::PRODUCT_URLS_CACHE_NAMESPACE, $key)) {
            return $this->inMemoryCache->getItem(static::PRODUCT_URLS_CACHE_NAMESPACE, $key);
        }

        throw new ProductUrlNotLoadedException($product, $domainConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string|null
     */
    public function getProductImageUrl(Product $product, DomainConfig $domainConfig): ?string
    {
        $key = $this->getKey($product, $domainConfig);

        if ($this->inMemoryCache->hasItem(static::PRODUCT_IMAGE_URLS_CACHE_NAMESPACE, $key)) {
            return $this->inMemoryCache->getItem(static::PRODUCT_IMAGE_URLS_CACHE_NAMESPACE, $key);
        }

        throw new ProductImageUrlNotLoadedException($product, $domainConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string|null
     */
    public function getResizedProductImageUrl(Product $product, DomainConfig $domainConfig): ?string
    {
        $imageUrl = $this->getProductImageUrl($product, $domainConfig);

        return $imageUrl !== null ? ImageUrlWithSizeHelper::limitSizeInImageUrl($imageUrl) : null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string
     */
    protected function getKey(Product $product, DomainConfig $domainConfig): string
    {
        return $domainConfig->getId() . '-' . $product->getId();
    }
}
