<?php

declare(strict_types=1);

namespace App\Model\Product\Filter\Elasticsearch;

use App\Model\Product\Search\FilterQueryFactory;
use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class ProductFilterElasticFacade
{
    /**
     * @var \Elasticsearch\Client
     */
    private Client $client;

    /**
     * @var \App\Model\Product\Search\FilterQueryFactory
     */
    private FilterQueryFactory $filterQueryFactory;

    /**
     * @var \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigIdsDataFactory
     */
    private ProductFilterConfigIdsDataFactory $productFilterConfigIdsDataFactory;

    /**
     * @param \Elasticsearch\Client $client
     * @param \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     * @param \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigIdsDataFactory $productFilterConfigIdsDataFactory
     */
    public function __construct(
        Client $client,
        FilterQueryFactory $filterQueryFactory,
        ProductFilterConfigIdsDataFactory $productFilterConfigIdsDataFactory
    ) {
        $this->client = $client;
        $this->filterQueryFactory = $filterQueryFactory;
        $this->productFilterConfigIdsDataFactory = $productFilterConfigIdsDataFactory;
    }

    /**
     * @param int $categoryId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigIdsData
     */
    public function getProductFilterDataInCategory(int $categoryId, PricingGroup $pricingGroup): ProductFilterConfigIdsData
    {
        $aggregationQuery = $this->filterQueryFactory->createVisible()
            ->filterOnlySellable()
            ->filterByCategory([$categoryId])
            ->getAggregationQueryForProductFilterConfig($pricingGroup->getId());
        $aggregationResult = $this->client->search($aggregationQuery)['aggregations'];

        return $this->productFilterConfigIdsDataFactory->createFromElasticsearchAggregationResult($aggregationResult);
    }

    /**
     * @param string|null $searchText
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigIdsData
     */
    public function getProductFilterDataForSearch(?string $searchText, PricingGroup $pricingGroup): ProductFilterConfigIdsData
    {
        $searchText = $searchText ?? '';

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
     * @return \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigIdsData
     */
    public function getProductFilterDataInBrand(int $brandId, PricingGroup $pricingGroup): ProductFilterConfigIdsData
    {
        $aggregationQuery = $this->filterQueryFactory->createVisible()
            ->filterOnlySellable()
            ->filterByBrands([$brandId])
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
