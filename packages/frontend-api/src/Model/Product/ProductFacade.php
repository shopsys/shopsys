<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\Exception\ProductNotFoundUserError;

class ProductFacade
{
    protected const string SELLABLE_PRODUCT_CACHE_NAMESPACE = 'sellableProductByUuid';

    public function __construct(
        protected readonly ProductRepository $productRepository,
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    public function getSellableByUuid(string $uuid, int $domainId, PricingGroup $pricingGroup): Product
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::SELLABLE_PRODUCT_CACHE_NAMESPACE,
            function () use ($uuid, $domainId, $pricingGroup): Product {
                try {
                    return $this->productRepository->getSellableByUuid($uuid, $domainId, $pricingGroup);
                } catch (ProductNotFoundException) {
                    throw new ProductNotFoundUserError(sprintf('Product with UUID "%s" not found', $uuid));
                }
            },
            $uuid,
            $domainId,
            $pricingGroup->getId(),
        );
    }

    public function getVisibleByUuid(string $uuid, int $domainId, PricingGroup $pricingGroup): Product
    {
        return $this->productRepository->getVisibleByUuid($uuid, $domainId, $pricingGroup);
    }

    public function getFilteredProductsCountOnCurrentDomain(
        ProductFilterData $productFilterData,
        string $search = '',
    ): int {
        $filterQuery = $this->filterQueryFactory->createListableWithProductFilter($productFilterData);

        if ($search !== '') {
            $filterQuery = $filterQuery->search($search);
        }

        return $this->productElasticsearchRepository->getProductsCountByFilterQuery($filterQuery);
    }

    public function getFilteredProductsOnCurrentDomain(
        int $limit,
        int $offset,
        string $orderingModeId,
        ProductFilterData $productFilterData,
        string $search = '',
    ): array {
        $filterQuery = $this->filterQueryFactory->createWithProductFilterData(
            $productFilterData,
            $orderingModeId,
            1,
            $limit,
        )->setFrom($offset);

        if ($search !== '') {
            $filterQuery = $filterQuery->search($search);
        }

        $productsResult = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery);

        return $productsResult->getHits();
    }
}
