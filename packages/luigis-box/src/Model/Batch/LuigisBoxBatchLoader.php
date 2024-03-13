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
        return self::$totalsByType[$type] ?? 0;
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData[] $luigisBoxBatchLoadData
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadByBatchData(array $luigisBoxBatchLoadData): Promise
    {
        $mainBatchLoadData = $this->getMainBatchLoadData($luigisBoxBatchLoadData);
        $query = $mainBatchLoadData->getQuery();
        $endpoint = $mainBatchLoadData->getEndpoint();
        $page = $mainBatchLoadData->getPage();
        $productFilter = $mainBatchLoadData->getFilter();
        $productOrderingMode = $mainBatchLoadData->getOrderingMode();
        $userIdentifier = $mainBatchLoadData->getUserIdentifier();
        $limitsByType = [];

        foreach ($luigisBoxBatchLoadData as $luigisBoxBatchLoadDataItem) {
            $limitsByType[$luigisBoxBatchLoadDataItem->getType()] = $luigisBoxBatchLoadDataItem->getLimit();
        }

        return $this->promiseAdapter->all($this->mapDataByTypes(
            $this->luigisBoxClient->getData($query, $endpoint, $page, $limitsByType, $productFilter, $userIdentifier, $productOrderingMode),
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

            self::$totalsByType[$type] = count($mappedDataOfCurrentType);
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
        $mainBatchLoadData = null;

        foreach ($luigisBoxBatchLoadData as $luigisBoxBatchLoadDataItem) {
            if ($luigisBoxBatchLoadDataItem->getType() === LuigisBoxClient::TYPE_IN_LUIGIS_BOX_PRODUCT) {
                $mainBatchLoadData = $luigisBoxBatchLoadDataItem;

                break;
            }
        }

        return $mainBatchLoadData ?? reset($luigisBoxBatchLoadData);
    }
}
