<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Product\Collection\Exception\ProductImageUrlNotLoadedException;
use Shopsys\FrameworkBundle\Model\Product\Collection\Exception\ProductParametersNotLoadedException;
use Shopsys\FrameworkBundle\Model\Product\Collection\Exception\ProductUrlNotLoadedException;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainFacade;

class HeurekaProductDataBatchLoader
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money[]|null[]
     */
    protected array $loadedProductCpcs = [];

    public function __construct(
        protected readonly ProductUrlsBatchLoader $productUrlsBatchLoader,
        protected readonly ProductParametersBatchLoader $productParametersBatchLoader,
        protected readonly HeurekaProductDomainFacade $heurekaProductDomainFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    public function loadForProducts(array $products, DomainConfig $domainConfig): void
    {
        $this->productUrlsBatchLoader->loadForProducts($products, $domainConfig);
        $this->productParametersBatchLoader->loadForProducts($products, $domainConfig);

        $heurekaProductDomainByProductId = $this->heurekaProductDomainFacade->getHeurekaProductDomainsByProductsAndDomainIndexedByProductId(
            $products,
            $domainConfig,
        );

        $this->loadedProductCpcs = [];

        foreach ($products as $product) {
            $key = $this->getKey($product, $domainConfig);
            $productId = $product->getId();

            $heurekaProductDomain = $heurekaProductDomainByProductId[$productId] ?? null;
            $this->loadedProductCpcs[$key] = $heurekaProductDomain !== null ? $heurekaProductDomain->getCpc() : null;
        }
    }

    public function getProductCpc(Product $product, DomainConfig $domainConfig): ?Money
    {
        $key = $this->getKey($product, $domainConfig);

        if (!array_key_exists($key, $this->loadedProductCpcs)) {
            throw new HeurekaProductDataNotLoadedException($product, $domainConfig, 'CPC');
        }

        return $this->loadedProductCpcs[$key];
    }

    /**
     * @return string[]
     */
    public function getProductParametersByName(Product $product, DomainConfig $domainConfig): array
    {
        try {
            return $this->productParametersBatchLoader->getProductParametersByName($product, $domainConfig);
        } catch (ProductParametersNotLoadedException $e) {
            throw new HeurekaProductDataNotLoadedException($product, $domainConfig, 'parameters', $e);
        }
    }

    public function getProductUrl(Product $product, DomainConfig $domainConfig): string
    {
        try {
            return $this->productUrlsBatchLoader->getProductUrl($product, $domainConfig);
        } catch (ProductUrlNotLoadedException $e) {
            throw new HeurekaProductDataNotLoadedException($product, $domainConfig, 'URL', $e);
        }
    }

    public function getProductImageUrl(Product $product, DomainConfig $domainConfig): ?string
    {
        try {
            return $this->productUrlsBatchLoader->getProductImageUrl($product, $domainConfig);
        } catch (ProductImageUrlNotLoadedException $e) {
            throw new HeurekaProductDataNotLoadedException($product, $domainConfig, 'URL for image', $e);
        }
    }

    protected function getKey(Product $product, DomainConfig $domainConfig): string
    {
        return $domainConfig->getId() . '-' . $product->getId();
    }
}
