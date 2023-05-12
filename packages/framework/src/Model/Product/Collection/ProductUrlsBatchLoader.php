<?php

namespace Shopsys\FrameworkBundle\Model\Product\Collection;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Product\Collection\Exception\ProductImageUrlNotLoadedException;
use Shopsys\FrameworkBundle\Model\Product\Collection\Exception\ProductUrlNotLoadedException;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductUrlsBatchLoader
{
    /**
     * @var string[]
     */
    protected array $loadedProductUrls = [];

    /**
     * @var string[]|null[]
     */
    protected array $loadedProductImageUrls = [];

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade $productCollectionFacade
     */
    public function __construct(protected readonly ProductCollectionFacade $productCollectionFacade)
    {
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
            $domainConfig
        );

        foreach ($products as $product) {
            $key = $this->getKey($product, $domainConfig);
            $productId = $product->getId();

            $this->loadedProductUrls[$key] = $productUrlsById[$productId];
            $this->loadedProductImageUrls[$key] = $productImageUrlsById[$productId];
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

        if (!array_key_exists($key, $this->loadedProductUrls)) {
            throw new ProductUrlNotLoadedException($product, $domainConfig);
        }

        return $this->loadedProductUrls[$key];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string|null
     */
    public function getProductImageUrl(Product $product, DomainConfig $domainConfig): ?string
    {
        $key = $this->getKey($product, $domainConfig);

        if (!array_key_exists($key, $this->loadedProductImageUrls)) {
            throw new ProductImageUrlNotLoadedException($product, $domainConfig);
        }

        return $this->loadedProductImageUrls[$key];
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
