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

    /**
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient $luigisBoxClient
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     * @param \Shopsys\FrontendApiBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\CombinedArticle\CombinedArticleElasticsearchFacade $combinedArticleElasticsearchFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     */
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
     * @param string $type
     * @return int
     */
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
     * @return \GraphQL\Executor\Promise\Promise
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
                    $mainBatchLoadData->getQuery(),
                    $mainBatchLoadData->getEndpoint(),
                    $mainBatchLoadData->getPage(),
                    $limitsByType,
                    $mainBatchLoadData->getFilter(),
                    $mainBatchLoadData->getUserIdentifier(),
                    $mainBatchLoadData->getOrderingMode(),
                    $mainBatchLoadData->getFacetNames(),
                ),
                $limitsByType,
                $mainBatchLoadData->getEndpoint(),
            ),
        );
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult[] $luigisBoxResults
     * @param array $limitsByType
     * @param string $endpoint
     * @return array
     */
    protected function mapDataByTypes(array $luigisBoxResults, array $limitsByType, string $endpoint): array
    {
        $mappedData = [];

        foreach ($limitsByType as $type => $limit) {
            $mappedDataOfCurrentType = [];

            if ($type === LuigisBoxClient::TYPE_IN_LUIGIS_BOX_PRODUCT) {
                $mappedDataOfCurrentType = $this->mapProductData($luigisBoxResults[$type], $limit);
            }

            if ($type === LuigisBoxClient::TYPE_IN_LUIGIS_BOX_CATEGORY) {
                $mappedDataOfCurrentType = $this->mapCategoryData($luigisBoxResults[$type]);
            }

            if ($type === LuigisBoxClient::TYPE_IN_LUIGIS_BOX_ARTICLE) {
                $mappedDataOfCurrentType = $this->mapArticleData($luigisBoxResults[$type]);
            }

            if ($type === LuigisBoxClient::TYPE_IN_LUIGIS_BOX_BRAND) {
                $mappedDataOfCurrentType = $this->mapBrandData($luigisBoxResults[$type]);
            }

            if ($endpoint === LuigisBoxClient::ACTION_SEARCH && $type === $this->getMainType()) {
                static::$facets = $luigisBoxResults[$type]->getFacets();
                static::$totalsByType[$type] = $luigisBoxResults[$type]->getItemsCount();
            } else {
                static::$totalsByType[$type] = -1;
            }

            $mappedData[] = $mappedDataOfCurrentType;
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
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult $luigisBoxResult
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    protected function mapCategoryData(LuigisBoxResult $luigisBoxResult): array
    {
        $categoryArray = $this->categoryFacade->getVisibleCategoriesByIds([$luigisBoxResult->getIds()], $this->domain->getCurrentDomainConfig());

        return reset($categoryArray);
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult $luigisBoxResult
     * @return array
     */
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
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult $luigisBoxResult
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    protected function mapBrandData(LuigisBoxResult $luigisBoxResult): array
    {
        return $this->brandFacade->getBrandsByIds($luigisBoxResult->getIds());
    }

    /**
     * @param array $luigisBoxBatchLoadData
     * @return \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData
     */
    protected function getMainBatchLoadData(array $luigisBoxBatchLoadData): LuigisBoxBatchLoadData
    {
        if ($this->mainBatchLoadData !== null) {
            return $this->mainBatchLoadData;
        }

        foreach ($luigisBoxBatchLoadData as $luigisBoxBatchLoadDataItem) {
            if ($luigisBoxBatchLoadDataItem->getType() === LuigisBoxClient::TYPE_IN_LUIGIS_BOX_PRODUCT) {
                $this->mainBatchLoadData = $luigisBoxBatchLoadDataItem;

                break;
            }
        }

        if ($this->mainBatchLoadData === null) {
            $this->mainBatchLoadData = reset($luigisBoxBatchLoadData);
        }

        return $this->mainBatchLoadData;
    }

    /**
     * @return string
     */
    protected function getMainType(): string
    {
        return $this->mainBatchLoadData->getType();
    }
}
