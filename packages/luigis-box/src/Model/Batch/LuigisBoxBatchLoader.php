<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Batch;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\CombinedArticle\CombinedArticleElasticsearchFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\FrontendApiBundle\Model\Category\CategoryFacade;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult;
use Shopsys\LuigisBoxBundle\Model\Endpoint\LuigisBoxEndpointEnum;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;

class LuigisBoxBatchLoader
{
    public function __construct(
        protected readonly LuigisBoxClient $luigisBoxClient,
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly CategoryFacade $categoryFacade,
        protected readonly Domain $domain,
        protected readonly CombinedArticleElasticsearchFacade $combinedArticleElasticsearchFacade,
        protected readonly BrandFacade $brandFacade,
    ) {
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData[] $luigisBoxBatchLoadData
     */
    public function loadByBatchData(array $luigisBoxBatchLoadData): Promise
    {
        $luigisBoxResultsByKey = $this->loadResultsByOriginalKey($luigisBoxBatchLoadData);

        return $this->promiseAdapter->all(
            $this->mapResultsByOriginalOrder(
                $luigisBoxResultsByKey,
                $luigisBoxBatchLoadData,
            ),
        );
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData[] $luigisBoxBatchLoadData
     * @return array<int|string, \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult>
     */
    protected function loadResultsByOriginalKey(array $luigisBoxBatchLoadData): array
    {
        $luigisBoxResultsByKey = [];

        foreach ($this->loadSearchResultsByIndependentRequests($luigisBoxBatchLoadData) as $key => $luigisBoxResult) {
            $luigisBoxResultsByKey[$key] = $luigisBoxResult;
        }

        foreach ($this->loadAutocompleteResultsByGroupedTypeRequests($luigisBoxBatchLoadData) as $key => $luigisBoxResult) {
            $luigisBoxResultsByKey[$key] = $luigisBoxResult;
        }

        foreach ($this->loadRecommendationResultsBySingleRequests($luigisBoxBatchLoadData) as $key => $luigisBoxResult) {
            $luigisBoxResultsByKey[$key] = $luigisBoxResult;
        }

        return $luigisBoxResultsByKey;
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData[] $luigisBoxBatchLoadData
     * @return array<string, int>
     */
    protected function getLimitsByType(array $luigisBoxBatchLoadData): array
    {
        $limitsByType = [];

        foreach ($luigisBoxBatchLoadData as $luigisBoxBatchLoadDataItem) {
            $limitsByType[$luigisBoxBatchLoadDataItem->getType()] = $luigisBoxBatchLoadDataItem->getLimit();
        }

        return $limitsByType;
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData[] $luigisBoxBatchLoadData
     * @return array<int|string, \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult>
     */
    protected function loadSearchResultsByIndependentRequests(array $luigisBoxBatchLoadData): array
    {
        $searchBatchLoadData = [];

        foreach ($luigisBoxBatchLoadData as $key => $luigisBoxBatchLoadDataItem) {
            if ($luigisBoxBatchLoadDataItem->getEndpoint() === LuigisBoxEndpointEnum::SEARCH) {
                $searchBatchLoadData[$key] = $luigisBoxBatchLoadDataItem;
            }
        }

        if ($searchBatchLoadData === []) {
            return [];
        }

        $resultsByKey = $this->luigisBoxClient->getDataForMultiple($searchBatchLoadData);
        $luigisBoxResultsByKey = [];

        foreach ($searchBatchLoadData as $key => $luigisBoxBatchLoadDataItem) {
            $type = $luigisBoxBatchLoadDataItem->getType();
            $luigisBoxResultsByKey[$key] = $resultsByKey[$key][$type];
        }

        return $luigisBoxResultsByKey;
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData[] $luigisBoxBatchLoadData
     * @return array<int|string, \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult>
     */
    protected function loadAutocompleteResultsByGroupedTypeRequests(array $luigisBoxBatchLoadData): array
    {
        $luigisBoxResultsByKey = [];

        foreach ($this->getAutocompleteBatchLoadDataGroups($luigisBoxBatchLoadData) as $batchLoadDataGroup) {
            $limitsByType = $this->getLimitsByType($batchLoadDataGroup);
            $firstBatchLoadData = array_first($batchLoadDataGroup);

            $luigisBoxResults = $this->luigisBoxClient->getData($firstBatchLoadData, $limitsByType);

            foreach ($batchLoadDataGroup as $key => $batchLoadData) {
                $type = $batchLoadData->getType();
                $luigisBoxResultsByKey[$key] = $luigisBoxResults[$type];
            }
        }

        return $luigisBoxResultsByKey;
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData[] $luigisBoxBatchLoadData
     * @return array<int|string, \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult>
     */
    protected function loadRecommendationResultsBySingleRequests(array $luigisBoxBatchLoadData): array
    {
        $luigisBoxResultsByKey = [];

        foreach ($luigisBoxBatchLoadData as $key => $batchLoadData) {
            if ($batchLoadData->getEndpoint() !== LuigisBoxEndpointEnum::RECOMMENDATIONS) {
                continue;
            }

            $type = $batchLoadData->getType();
            $luigisBoxResults = $this->luigisBoxClient->getData($batchLoadData, [$type => $batchLoadData->getLimit()]);
            $luigisBoxResultsByKey[$key] = $luigisBoxResults[$type];
        }

        return $luigisBoxResultsByKey;
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData[] $luigisBoxBatchLoadData
     * @return array<int, array<int|string, \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData>>
     */
    protected function getAutocompleteBatchLoadDataGroups(array $luigisBoxBatchLoadData): array
    {
        $groups = [];
        $groupKeys = [];
        $typesByGroup = [];

        foreach ($luigisBoxBatchLoadData as $key => $luigisBoxBatchLoadDataItem) {
            if ($luigisBoxBatchLoadDataItem->getEndpoint() !== LuigisBoxEndpointEnum::AUTOCOMPLETE) {
                continue;
            }

            $groupKey = $this->getAutocompleteGroupKey($luigisBoxBatchLoadDataItem);
            $groupIndex = $this->getAutocompleteGroupIndex($groupKeys, $typesByGroup, $groupKey, $luigisBoxBatchLoadDataItem->getType());

            $groupKeys[$groupIndex] = $groupKey;
            $groups[$groupIndex][$key] = $luigisBoxBatchLoadDataItem;
            $typesByGroup[$groupIndex][$luigisBoxBatchLoadDataItem->getType()] = true;
        }

        return $groups;
    }

    protected function getAutocompleteGroupKey(LuigisBoxBatchLoadData $luigisBoxBatchLoadData): string
    {
        if (!$luigisBoxBatchLoadData instanceof LuigisBoxSearchBatchLoadData) {
            return $luigisBoxBatchLoadData->getEndpoint() . '|' . $luigisBoxBatchLoadData->getUserIdentifier();
        }

        return implode('|', [
            $luigisBoxBatchLoadData->getEndpoint(),
            $luigisBoxBatchLoadData->getUserIdentifier(),
            $luigisBoxBatchLoadData->getQuery(),
        ]);
    }

    /**
     * @param array<int, string> $groupKeys
     * @param array<int, array<string, bool>> $typesByGroup
     */
    protected function getAutocompleteGroupIndex(
        array $groupKeys,
        array $typesByGroup,
        string $groupKey,
        string $type,
    ): int {
        foreach ($groupKeys as $index => $existingGroupKey) {
            if (
                $existingGroupKey === $groupKey
                && array_key_exists($type, $typesByGroup[$index]) === false
            ) {
                return $index;
            }
        }

        return count($groupKeys);
    }

    /**
     * @param array<int|string, \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult> $luigisBoxResultsByKey
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData[] $luigisBoxBatchLoadData
     * @return \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadResult[]
     */
    protected function mapResultsByOriginalOrder(array $luigisBoxResultsByKey, array $luigisBoxBatchLoadData): array
    {
        $mappedData = [];

        foreach ($luigisBoxBatchLoadData as $key => $batchLoadData) {
            $mappedData[] = $this->mapDataByType(
                $batchLoadData->getType(),
                $batchLoadData->getLimit(),
                $luigisBoxResultsByKey[$key],
            );
        }

        return $mappedData;
    }

    protected function mapDataByType(
        string $type,
        int $limit,
        LuigisBoxResult $luigisBoxResult,
    ): LuigisBoxBatchLoadResult {
        $mappedDataOfCurrentType = [];

        if ($type === TypeInLuigisBoxEnum::PRODUCT) {
            $mappedDataOfCurrentType = $this->mapProductData($luigisBoxResult, $limit);
        }

        if ($type === TypeInLuigisBoxEnum::CATEGORY) {
            $mappedDataOfCurrentType = $this->mapCategoryData($luigisBoxResult);
        }

        if ($type === TypeInLuigisBoxEnum::ARTICLE) {
            $mappedDataOfCurrentType = $this->mapArticleData($luigisBoxResult);
        }

        if ($type === TypeInLuigisBoxEnum::BRAND) {
            $mappedDataOfCurrentType = $this->mapBrandData($luigisBoxResult);
        }

        return new LuigisBoxBatchLoadResult(
            $mappedDataOfCurrentType,
            $luigisBoxResult->getItemsCount(),
            $luigisBoxResult->getFacets(),
        );
    }

    protected function mapProductData(LuigisBoxResult $luigisBoxResult, int $limit): array
    {
        $filterQuery = $this->filterQueryFactory->createSellableProductsByProductIdsFilter(
            $luigisBoxResult->getIds(),
            $limit,
        );

        return $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery)->getHits();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    protected function mapCategoryData(LuigisBoxResult $luigisBoxResult): array
    {
        $categoryArray = $this->categoryFacade->getVisibleCategoriesByIds(
            [$luigisBoxResult->getIds()],
            $this->domain->getCurrentDomainConfig(),
        );

        return array_first($categoryArray);
    }

    protected function mapArticleData(LuigisBoxResult $luigisBoxResult): array
    {
        if (count($luigisBoxResult->getIdsWithPrefix()) === 0) {
            return [];
        }

        $idsByType = [];

        foreach ($luigisBoxResult->getIdsWithPrefix() as $idWithPrefix) {
            [$type, $id] = explode('-', $idWithPrefix);
            $idsByType[$type][] = $id;
        }

        return $this->combinedArticleElasticsearchFacade->getArticlesByIds(
            $idsByType,
            $this->domain->getId(),
            count($luigisBoxResult->getIds()),
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    protected function mapBrandData(LuigisBoxResult $luigisBoxResult): array
    {
        return $this->brandFacade->getBrandsByIds($luigisBoxResult->getIds());
    }
}
