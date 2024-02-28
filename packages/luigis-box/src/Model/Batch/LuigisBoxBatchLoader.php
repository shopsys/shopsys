<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Batch;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult;
use Shopsys\LuigisBoxBundle\Model\Batch\Exception\ProductSearchIsMandatoryForAllLuigisBoxSearchesUserError;

class LuigisBoxBatchLoader
{
    /**
     * @var array<string, int>
     */
    protected static array $totalsByType = [];

    /**
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient $luigisBoxClient
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     */
    public function __construct(
        protected readonly LuigisBoxClient $luigisBoxClient,
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
        protected readonly FilterQueryFactory $filterQueryFactory,
    ) {
    }

    /**
     * @param string $type
     * @return int
     */
    public static function getTotalByType(string $type): int
    {
        return self::$totalsByType[$type] ?? 0;
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData[] $luigisBoxBatchLoadData
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadByBatchData(array $luigisBoxBatchLoadData): Promise
    {
        $this->validateProductSearchIsPresent($luigisBoxBatchLoadData);

        $query = '';
        $limitsByType = [];
        $endpoint = '';
        $page = 0;
        $productFilter = [];
        $productOrderingMode = null;

        foreach ($luigisBoxBatchLoadData as $luigisBoxBatchLoadDataItem) {
            if ($luigisBoxBatchLoadDataItem->getType() === LuigisBoxClient::MAIN_TYPE) {
                $query = $luigisBoxBatchLoadDataItem->getQuery();
                $endpoint = $luigisBoxBatchLoadDataItem->getEndpoint();
                $page = $luigisBoxBatchLoadDataItem->getPage();
                $productFilter = $luigisBoxBatchLoadDataItem->getFilter();
                $productOrderingMode = $luigisBoxBatchLoadDataItem->getOrderingMode();
            }

            $limitsByType[$luigisBoxBatchLoadDataItem->getType()] = $luigisBoxBatchLoadDataItem->getLimit();
        }

        return $this->promiseAdapter->all($this->mapDataByTypes(
            $this->luigisBoxClient->getData($query, $endpoint, $page, $limitsByType, $productFilter, $productOrderingMode),
            $limitsByType,
        ));
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult[] $luigisBoxResults
     * @param array $limitsByType
     * @return array
     */
    protected function mapDataByTypes(array $luigisBoxResults, array $limitsByType): array
    {
        $mappedData = [];

        foreach ($limitsByType as $type => $limit) {
            $mappedDataOfCurrentType = [];

            if ($type === 'product') {
                $mappedDataOfCurrentType = $this->mapProductData($luigisBoxResults[$type], $limit);
            }

            $mappedData[] = $mappedDataOfCurrentType;
            self::$totalsByType[$type] = count($mappedDataOfCurrentType) > 0 ? $luigisBoxResults[$type]->getItemsCount() : 0;
        }

        return $mappedData;
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult $luigisBoxResult
     * @param int $limit
     * @return array
     */
    protected function mapProductData(LuigisBoxResult $luigisBoxResult, int $limit): array
    {
        $filterQuery = $this->filterQueryFactory->createSellableProductsByProductIdsFilter($luigisBoxResult->getIds(), $limit);

        return $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery)->getHits();
    }

    /**
     * @param array $luigisBoxBatchLoadData
     */
    protected function validateProductSearchIsPresent(array $luigisBoxBatchLoadData): void
    {
        $productSearchIsPresent = false;

        foreach ($luigisBoxBatchLoadData as $luigisBoxBatchLoadDataItem) {
            if ($luigisBoxBatchLoadDataItem->getType() === LuigisBoxClient::MAIN_TYPE) {
                $productSearchIsPresent = true;

                break;
            }
        }

        if (!$productSearchIsPresent) {
            throw new ProductSearchIsMandatoryForAllLuigisBoxSearchesUserError();
        }
    }
}
