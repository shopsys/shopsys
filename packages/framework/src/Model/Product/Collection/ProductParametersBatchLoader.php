<?php

namespace Shopsys\FrameworkBundle\Model\Product\Collection;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductParametersBatchLoader
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade
     */
    protected $productCollectionFacade;

    /**
     * @var string[]|null[]
     */
    protected $loadedProductParametersByName = [];

    public function __construct(ProductCollectionFacade $productCollectionFacade)
    {
        $this->productCollectionFacade = $productCollectionFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    public function loadForProducts(array $products, DomainConfig $domainConfig): void
    {
        $parametersByProductIdAndName = $this->productCollectionFacade->getProductParameterValuesIndexedByProductIdAndParameterName($products, $domainConfig);

        foreach ($products as $product) {
            $key = $this->getKey($product, $domainConfig);
            $productId = $product->getId();

            $this->loadedProductParametersByName[$key] = $parametersByProductIdAndName[$productId] ?? [];
        }
    }

    /**
     * @return string[]
     */
    public function getProductParametersByName(Product $product, DomainConfig $domainConfig): array
    {
        $key = $this->getKey($product, $domainConfig);
        if (!array_key_exists($key, $this->loadedProductParametersByName)) {
            throw new \Shopsys\FrameworkBundle\Model\Product\Collection\Exception\ProductParametersNotLoadedException($product, $domainConfig);
        }

        return $this->loadedProductParametersByName[$key];
    }

    protected function getKey(Product $product, DomainConfig $domainConfig): string
    {
        return $domainConfig->getId() . '-' . $product->getId();
    }
}
