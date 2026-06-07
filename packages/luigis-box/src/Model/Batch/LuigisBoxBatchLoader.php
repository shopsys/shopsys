<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Batch;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult;
use Shopsys\LuigisBoxBundle\Model\Article\LuigisBoxArticleSearchResultsMapper;
use Shopsys\LuigisBoxBundle\Model\Brand\LuigisBoxBrandSearchResultsMapper;
use Shopsys\LuigisBoxBundle\Model\Category\LuigisBoxCategorySearchResultsMapper;
use Shopsys\LuigisBoxBundle\Model\Endpoint\LuigisBoxEndpointEnum;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;

class LuigisBoxBatchLoader
{
    /**
     * @var array<string, int>
     */
    protected static array $totalsByType = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    protected static array $facets = [];

    protected ?LuigisBoxBatchLoadData $mainBatchLoadData = null;

    public function __construct(
        protected readonly LuigisBoxClient $luigisBoxClient,
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly LuigisBoxCategorySearchResultsMapper $luigisBoxCategorySearchResultsMapper,
        protected readonly LuigisBoxArticleSearchResultsMapper $luigisBoxArticleSearchResultsMapper,
        protected readonly LuigisBoxBrandSearchResultsMapper $luigisBoxBrandSearchResultsMapper,
    ) {
    }

    public static function getTotalByType(string $type): int
    {
        return static::$totalsByType[$type] ?? 0;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getFacets(): array
    {
        return static::$facets;
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData[] $luigisBoxBatchLoadData
     */
    public function loadByBatchData(array $luigisBoxBatchLoadData): Promise
    {
        $mainBatchLoadData = $this->getMainBatchLoadData($luigisBoxBatchLoadData);
        $limitsByType = [];

        foreach ($luigisBoxBatchLoadData as $luigisBoxBatchLoadDataItem) {
            $limitsByType[$luigisBoxBatchLoadDataItem->getType()] = $luigisBoxBatchLoadDataItem->getLimit();
        }

        return $this->promiseAdapter->all(
            $this->mapDataByTypes(
                $this->luigisBoxClient->getData(
                    $mainBatchLoadData,
                    $limitsByType,
                ),
                $limitsByType,
                $mainBatchLoadData->getEndpoint(),
            ),
        );
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult[] $luigisBoxResults
     */
    protected function mapDataByTypes(array $luigisBoxResults, array $limitsByType, string $endpoint): array
    {
        $mappedData = [];

        foreach ($limitsByType as $type => $limit) {
            $mappedDataOfCurrentType = [];

            if ($type === TypeInLuigisBoxEnum::PRODUCT) {
                $mappedDataOfCurrentType = $this->mapProductData($luigisBoxResults[$type], $limit);
            }

            if ($type === TypeInLuigisBoxEnum::CATEGORY) {
                $mappedDataOfCurrentType = $this->luigisBoxCategorySearchResultsMapper->mapCategoryData($luigisBoxResults[$type]);
            }

            if ($type === TypeInLuigisBoxEnum::ARTICLE) {
                $mappedDataOfCurrentType = $this->luigisBoxArticleSearchResultsMapper->mapArticleData($luigisBoxResults[$type]);
            }

            if ($type === TypeInLuigisBoxEnum::BRAND) {
                $mappedDataOfCurrentType = $this->luigisBoxBrandSearchResultsMapper->mapBrandData($luigisBoxResults[$type]);
            }

            if ($endpoint === LuigisBoxEndpointEnum::SEARCH && $type === $this->getMainType()) {
                static::$facets = $luigisBoxResults[$type]->getFacets();
                static::$totalsByType[$type] = $luigisBoxResults[$type]->getItemsCount();
            } else {
                static::$totalsByType[$type] = -1;
            }

            $mappedData[] = $mappedDataOfCurrentType;
        }

        return $mappedData;
    }

    protected function mapProductData(LuigisBoxResult $luigisBoxResult, int $limit): array
    {
        $filterQuery = $this->filterQueryFactory->createSellableProductsByProductIdsFilter($luigisBoxResult->getIds(), $limit);

        return $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery)->getHits();
    }

    protected function getMainBatchLoadData(array $luigisBoxBatchLoadData): LuigisBoxBatchLoadData
    {
        if ($this->mainBatchLoadData !== null) {
            return $this->mainBatchLoadData;
        }

        foreach ($luigisBoxBatchLoadData as $luigisBoxBatchLoadDataItem) {
            if ($luigisBoxBatchLoadDataItem->getType() === TypeInLuigisBoxEnum::PRODUCT) {
                $this->mainBatchLoadData = $luigisBoxBatchLoadDataItem;

                break;
            }
        }

        if ($this->mainBatchLoadData === null) {
            $this->mainBatchLoadData = array_first($luigisBoxBatchLoadData);
        }

        return $this->mainBatchLoadData;
    }

    protected function getMainType(): string
    {
        return $this->mainBatchLoadData->getType();
    }
}
