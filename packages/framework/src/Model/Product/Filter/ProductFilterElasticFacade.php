<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;

class ProductFilterElasticFacade
{
    public function __construct(
        protected readonly Client $client,
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly ProductFilterConfigIdsDataFactory $productFilterConfigIdsDataFactory,
    ) {
    }

    public function getProductFilterDataInCategory(
        Category $category,
        PricingGroup $pricingGroup,
    ): ProductFilterConfigIdsData {
        return $this->getProductFilterConfigIdsData($this->getAggregationQueryInCategory($category, $pricingGroup));
    }

    public function getProductFilterDataInBrand(
        int $brandId,
        PricingGroup $pricingGroup,
    ): ProductFilterConfigIdsData {
        return $this->getProductFilterConfigIdsData($this->getAggregationQueryInBrand($brandId, $pricingGroup));
    }

    /**
     * @return array{index: string, body: array<string, mixed>}
     */
    public function getAggregationQueryInCategory(Category $category, PricingGroup $pricingGroup): array
    {
        return $this->filterQueryFactory->createVisibleForCategory($category)
            ->filterOnlySellable()
            ->getAggregationQueryForProductFilterConfig($pricingGroup->getId());
    }

    /**
     * @return array{index: string, body: array<string, mixed>}
     */
    public function getAggregationQueryForSearch(string $searchText, PricingGroup $pricingGroup): array
    {
        return $this->filterQueryFactory->createVisible()
            ->filterOnlySellable()
            ->search($searchText)
            ->getAggregationQueryForProductFilterConfigWithoutParameters($pricingGroup->getId());
    }

    /**
     * @return array{index: string, body: array<string, mixed>}
     */
    public function getAggregationQueryInBrand(int $brandId, PricingGroup $pricingGroup): array
    {
        return $this->filterQueryFactory->createVisible()
            ->filterOnlySellable()
            ->filterByBrands([$brandId])
            ->getAggregationQueryForProductFilterConfig($pricingGroup->getId());
    }

    /**
     * @return array{index: string, body: array<string, mixed>}
     */
    public function getAggregationQueryInFlag(int $flagId, PricingGroup $pricingGroup): array
    {
        return $this->filterQueryFactory->createVisible()
            ->filterOnlySellable()
            ->filterByFlags([$flagId])
            ->getAggregationQueryForProductFilterConfig($pricingGroup->getId());
    }

    /**
     * @return array{index: string, body: array<string, mixed>}
     */
    public function getAggregationQueryForAll(PricingGroup $pricingGroup): array
    {
        return $this->filterQueryFactory->createVisible()
            ->filterOnlySellable()
            ->getAggregationQueryForProductFilterConfig($pricingGroup->getId());
    }

    /**
     * @param array<string, mixed> $aggregationResponse
     */
    public function createProductFilterConfigIdsData(array $aggregationResponse): ProductFilterConfigIdsData
    {
        return $this->productFilterConfigIdsDataFactory->createFromElasticsearchAggregationResult($aggregationResponse['aggregations']);
    }

    /**
     * @param array{index: string, body: array<string, mixed>} $aggregationQuery
     */
    public function getProductFilterConfigIdsData(array $aggregationQuery): ProductFilterConfigIdsData
    {
        return $this->createProductFilterConfigIdsData($this->client->search($aggregationQuery));
    }
}
