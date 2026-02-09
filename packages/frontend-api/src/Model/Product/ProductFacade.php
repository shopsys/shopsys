<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;

class ProductFacade
{
    public function __construct(
        protected readonly ProductRepository $productRepository,
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
    ) {
    }

    public function getSellableByUuid(string $uuid, int $domainId, PricingGroup $pricingGroup): Product
    {
        return $this->productRepository->getSellableByUuid($uuid, $domainId, $pricingGroup);
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

    public function getSellableProductsByIds(array $productIds, ?int $limit = null): array
    {
        $filterQuery = $this->filterQueryFactory->createSellableProductsByProductIdsFilter($productIds, $limit);

        $productsResult = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery);

        return $productsResult->getHits();
    }
}
