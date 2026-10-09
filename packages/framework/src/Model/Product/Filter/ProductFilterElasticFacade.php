<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Component\Elasticsearch\MultipleSearchFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;

class ProductFilterElasticFacade
{
    public function __construct(
        protected readonly MultipleSearchFacade $multipleSearchFacade,
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly ProductFilterConfigIdsDataFactory $productFilterConfigIdsDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterBatchLoadData[] $batchLoadDataIndexedByKey
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigIdsData[] indexed by the same keys as the batch load data
     */
    public function getProductFilterConfigIdsDataByBatchLoadData(
        array $batchLoadDataIndexedByKey,
        PricingGroup $pricingGroup,
    ): array {
        $aggregationQueriesIndexedByKey = [];

        foreach ($batchLoadDataIndexedByKey as $key => $batchLoadData) {
            $aggregationQueriesIndexedByKey[$key] = $this->createAggregationQuery($batchLoadData, $pricingGroup);
        }

        $responsesIndexedByKey = $this->multipleSearchFacade->searchIndexedByKey($aggregationQueriesIndexedByKey);
        $productFilterConfigIdsDataIndexedByKey = [];

        foreach ($responsesIndexedByKey as $key => $response) {
            $productFilterConfigIdsDataIndexedByKey[$key] = $this->productFilterConfigIdsDataFactory->createFromElasticsearchAggregationResult(
                $response['aggregations'],
            );
        }

        return $productFilterConfigIdsDataIndexedByKey;
    }

    /**
     * @return array{index: string, body: array<string, mixed>}
     */
    protected function createAggregationQuery(
        ProductFilterBatchLoadData $batchLoadData,
        PricingGroup $pricingGroup,
    ): array {
        $entity = $batchLoadData->getEntity();
        $pricingGroupId = $pricingGroup->getId();

        return match (true) {
            $entity instanceof Category => $this->filterQueryFactory->createVisibleForCategory($entity)
                ->filterOnlySellable()
                ->getAggregationQueryForProductFilterConfig($pricingGroupId),
            $entity instanceof Brand => $this->createVisibleSellableFilterQuery()
                ->filterByBrands([$entity->getId()])
                ->getAggregationQueryForProductFilterConfigWithoutParameters($pricingGroupId),
            $entity instanceof Flag => $this->createVisibleSellableFilterQuery()
                ->filterByFlags([$entity->getId()])
                ->getAggregationQueryForProductFilterConfigWithoutParameters($pricingGroupId),
            $batchLoadData->getSearchText() !== '' => $this->createVisibleSellableFilterQuery()
                ->search($batchLoadData->getSearchText())
                ->getAggregationQueryForProductFilterConfigWithoutParameters($pricingGroupId),
            default => $this->createVisibleSellableFilterQuery()
                ->getAggregationQueryForProductFilterConfigWithoutParameters($pricingGroupId),
        };
    }

    protected function createVisibleSellableFilterQuery(): FilterQuery
    {
        return $this->filterQueryFactory->createVisible()
            ->filterOnlySellable();
    }
}
