<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Collection;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\LocalCache\LocalCacheFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\Exception\ProductParametersNotLoadedException;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductParametersBatchLoader
{
    protected const PARAMETERS_CACHE_NAMESPACE = 'parametersByProductIdAndName';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade $productCollectionFacade
     * @param \Shopsys\FrameworkBundle\Component\LocalCache\LocalCacheFacade $localCacheFacade
     */
    public function __construct(
        protected readonly ProductCollectionFacade $productCollectionFacade,
        protected readonly LocalCacheFacade $localCacheFacade,
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

            $this->localCacheFacade->save(static::PARAMETERS_CACHE_NAMESPACE, $key, $parametersByProductIdAndName[$productId] ?? []);
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

        if (!$this->localCacheFacade->hasItem(static::PARAMETERS_CACHE_NAMESPACE, $key)) {
            throw new ProductParametersNotLoadedException($product, $domainConfig);
        }

        return $this->localCacheFacade->getItem(static::PARAMETERS_CACHE_NAMESPACE, $key);
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
