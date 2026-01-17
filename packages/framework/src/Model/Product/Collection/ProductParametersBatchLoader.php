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

    public function __construct(
        protected readonly ProductCollectionFacade $productCollectionFacade,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
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

    protected function getKey(Product $product, DomainConfig $domainConfig): string
    {
        return $domainConfig->getId() . '-' . $product->getId();
    }
}
