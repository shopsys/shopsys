<?php

declare(strict_types=1);

namespace App\Model\Product\Filter\Elasticsearch;

use App\Model\Product\Search\FilterQueryFactory;
use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class ProductFilterElasticFacade
{
    /**
     * @param \Elasticsearch\Client $client
     * @param \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     * @param \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigIdsDataFactory $productFilterConfigIdsDataFactory
     */
    public function __construct(
        private Client $client,
        private FilterQueryFactory $filterQueryFactory,
        private ProductFilterConfigIdsDataFactory $productFilterConfigIdsDataFactory,
    ) {
    }

    /**
     * @param int $categoryId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param string $search
     * @return \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigIdsData
     */
    public function getProductFilterDataInCategory(
        int $categoryId,
        PricingGroup $pricingGroup,
        string $search,
    ): ProductFilterConfigIdsData {
        $filterQuery = $this->filterQueryFactory->createVisible()
            ->filterOnlySellable()
            ->filterByCategory([$categoryId]);
        if ($search !== '') {
            $filterQuery = $filterQuery->search($search);
        }
        $aggregationQuery = $filterQuery
            ->getAggregationQueryForProductFilterConfig($pricingGroup->getId());
        $aggregationResult = $this->client->search($aggregationQuery)['aggregations'];

        return $this->productFilterConfigIdsDataFactory->createFromElasticsearchAggregationResult($aggregationResult);
    }

    /**
     * @param string $searchText
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigIdsData
     */
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

    /**
     * @param int $brandId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param string $searchText
     * @return \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigIdsData
     */
    public function getProductFilterDataInBrand(
        int $brandId,
        PricingGroup $pricingGroup,
        string $searchText = '',
    ): ProductFilterConfigIdsData {
        $filterQuery = $this->filterQueryFactory->createVisible()
            ->filterOnlySellable()
            ->filterByBrands([$brandId]);
        if ($searchText !== '') {
            $filterQuery = $filterQuery->search($searchText);
        }
        $aggregationQuery = $filterQuery
            ->getAggregationQueryForProductFilterConfig($pricingGroup->getId());
        $aggregationResult = $this->client->search($aggregationQuery)['aggregations'];

        return $this->productFilterConfigIdsDataFactory->createFromElasticsearchAggregationResult($aggregationResult);
    }

    /**
     * @param int $flagId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param string $searchText
     * @return \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigIdsData
     */
    public function getProductFilterDataInFlag(
        int $flagId,
        PricingGroup $pricingGroup,
        string $searchText = '',
    ): ProductFilterConfigIdsData {
        $filterQuery = $this->filterQueryFactory->createVisible()
            ->filterOnlySellable()
            ->filterByFlags([$flagId]);
        if ($searchText !== '') {
            $filterQuery = $filterQuery->search($searchText);
        }
        $aggregationQuery = $filterQuery
            ->getAggregationQueryForProductFilterConfig($pricingGroup->getId());
        $aggregationResult = $this->client->search($aggregationQuery)['aggregations'];

        return $this->productFilterConfigIdsDataFactory->createFromElasticsearchAggregationResult($aggregationResult);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigIdsData
     */
    public function getProductFilterDataForAll(PricingGroup $pricingGroup): ProductFilterConfigIdsData
    {
        $aggregationQuery = $this->filterQueryFactory->createVisible()
            ->filterOnlySellable()
            ->getAggregationQueryForProductFilterConfig($pricingGroup->getId());
        $aggregationResult = $this->client->search($aggregationQuery)['aggregations'];

        return $this->productFilterConfigIdsDataFactory->createFromElasticsearchAggregationResult($aggregationResult);
    }
}
