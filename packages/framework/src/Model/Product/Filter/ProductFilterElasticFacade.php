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
        $filterQuery = $this->filterQueryFactory->createVisibleForCategory($category)
            ->filterOnlySellable();

        $aggregationQuery = $filterQuery
            ->getAggregationQueryForProductFilterConfig($pricingGroup->getId());
        $aggregationResult = $this->client->search($aggregationQuery)['aggregations'];

        return $this->productFilterConfigIdsDataFactory->createFromElasticsearchAggregationResult($aggregationResult);
    }

    public function getProductFilterDataForSearch(
        string $searchText,
        PricingGroup $pricingGroup,
    ): ProductFilterConfigIdsData {
        $aggregationQuery = $this->filterQueryFactory->createVisible()
            ->filterOnlySellable()
            ->search($searchText)
            ->getAggregationQueryForProductFilterConfigWithoutParameters($pricingGroup->getId());
        $aggregationResult = $this->client->search($aggregationQuery)['aggregations'];

        return $this->productFilterConfigIdsDataFactory->createFromElasticsearchAggregationResult($aggregationResult);
    }

    public function getProductFilterDataInBrand(
        int $brandId,
        PricingGroup $pricingGroup,
    ): ProductFilterConfigIdsData {
        $filterQuery = $this->filterQueryFactory->createVisible()
            ->filterOnlySellable()
            ->filterByBrands([$brandId]);

        $aggregationQuery = $filterQuery
            ->getAggregationQueryForProductFilterConfig($pricingGroup->getId());
        $aggregationResult = $this->client->search($aggregationQuery)['aggregations'];

        return $this->productFilterConfigIdsDataFactory->createFromElasticsearchAggregationResult($aggregationResult);
    }

    public function getProductFilterDataInFlag(
        int $flagId,
        PricingGroup $pricingGroup,
    ): ProductFilterConfigIdsData {
        $filterQuery = $this->filterQueryFactory->createVisible()
            ->filterOnlySellable()
            ->filterByFlags([$flagId]);

        $aggregationQuery = $filterQuery
            ->getAggregationQueryForProductFilterConfig($pricingGroup->getId());
        $aggregationResult = $this->client->search($aggregationQuery)['aggregations'];

        return $this->productFilterConfigIdsDataFactory->createFromElasticsearchAggregationResult($aggregationResult);
    }

    public function getProductFilterDataForAll(PricingGroup $pricingGroup): ProductFilterConfigIdsData
    {
        $aggregationQuery = $this->filterQueryFactory->createVisible()
            ->filterOnlySellable()
            ->getAggregationQueryForProductFilterConfig($pricingGroup->getId());
        $aggregationResult = $this->client->search($aggregationQuery)['aggregations'];

        return $this->productFilterConfigIdsDataFactory->createFromElasticsearchAggregationResult($aggregationResult);
    }
}
