<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Collection;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Product\Collection\Exception\ProductParametersNotLoadedException;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductParametersBatchLoader
{
    protected const string PARAMETERS_CACHE_NAMESPACE = 'parametersByProductIdAndName';

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
        $parametersByProductIdAndName = $this->productCollectionFacade->getProductParameterValuesIndexedByProductIdAndParameterName(
            $products,
            $domainConfig,
        );

        foreach ($products as $product) {
            $key = $this->getKey($product, $domainConfig);
            $productId = $product->getId();

            $this->inMemoryCache->save(static::PARAMETERS_CACHE_NAMESPACE, $parametersByProductIdAndName[$productId] ?? [], $key);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string[]
     */
    public function getProductParametersByName(Product $product, DomainConfig $domainConfig): array
    {
        $key = $this->getKey($product, $domainConfig);

        if ($this->inMemoryCache->hasItem(static::PARAMETERS_CACHE_NAMESPACE, $key)) {
            return $this->inMemoryCache->getItem(static::PARAMETERS_CACHE_NAMESPACE, $key);
        }

        throw new ProductParametersNotLoadedException($product, $domainConfig);
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
