<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use Doctrine\ORM\QueryBuilder;
use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

class ProductElasticsearchRepository
{
    protected const string FOUND_PRODUCT_IDS_CACHE_NAMESPACE = 'foundProductIds';

    public function __construct(
        protected readonly Client $client,
        protected readonly ProductElasticsearchConverter $productElasticsearchConverter,
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    public function filterBySearchText(QueryBuilder $productQueryBuilder, ?string $searchText): void
    {
        $productIds = $this->getFoundProductIds($productQueryBuilder, $searchText);

        if (count($productIds) > 0) {
            $productQueryBuilder->andWhere('p.id IN (:productIds)')->setParameter('productIds', $productIds);
        } else {
            $productQueryBuilder->andWhere('TRUE = FALSE');
        }
    }

    /**
     * @return int[]
     */
    protected function getFoundProductIds(QueryBuilder $productQueryBuilder, ?string $searchText): array
    {
        $domainId = $productQueryBuilder->getParameter('domainId')->getValue();

        return $this->inMemoryCache->getOrSaveValue(
            static::FOUND_PRODUCT_IDS_CACHE_NAMESPACE,
            fn () => $this->getProductIdsBySearchText($domainId, $searchText),
            $domainId,
            $searchText,
        );
    }

    /**
     * @return int[]
     */
    public function getProductIdsBySearchText(int $domainId, ?string $searchText): array
    {
        if ($searchText === null || $searchText === '') {
            return [];
        }

        $indexDefinition = $this->indexDefinitionLoader->getIndexDefinition(ProductIndex::getName(), $domainId);
        $parameters = $this->createQuery($indexDefinition->getIndexAlias(), $searchText);
        $result = $this->client->search($parameters);

        return $this->extractIds($result);
    }

    public function getSortedProductsResultByFilterQuery(FilterQuery $filterQuery): ProductsResult
    {
        $result = $this->client->search($filterQuery->getQuery());

        return new ProductsResult($this->extractTotalCount($result), $this->extractHits($result));
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html
     */
    protected function createQuery(string $indexName, string $searchText): array
    {
        $query = $this->filterQueryFactory->create($indexName)
            ->search($searchText);

        return $query->getQuery();
    }

    /**
     * @return int[]
     */
    protected function extractIds(array $result): array
    {
        $hits = $result['hits']['hits'];

        return array_column($hits, '_id');
    }

    public function extractHits(array $result): array
    {
        return array_map(function ($value) {
            $data = $value['_source'];

            if (!array_key_exists('id', $data)) {
                $data['id'] = (int)$value['_id'];
            }

            return $this->productElasticsearchConverter->fillEmptyFields($data);
        }, $result['hits']['hits']);
    }

    public function extractTotalCount(array $result): int
    {
        return (int)$result['hits']['total']['value'];
    }

    public function getProductsCountByFilterQuery(FilterQuery $filterQuery): int
    {
        $result = $this->client->search($filterQuery->getQuery());

        return $this->extractTotalCount($result);
    }

    public function getProductsByFilterQuery(FilterQuery $filterQuery): array
    {
        $result = $this->client->search($filterQuery->getQuery());

        return $this->extractHits($result);
    }

    /**
     * The reviews can grow into by far the largest field of the document and no product read uses them,
     * they are served separately by the frontend API with the pagination done by Elasticsearch
     *
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    protected function excludeReviewsFromSource(array $query): array
    {
        if (!array_key_exists('_source', $query['body'])) {
            $query['body']['_source'] = ['excludes' => [ProductExportFieldProvider::REVIEWS]];
        }

        return $query;
    }

    /**
     * @param int[] $productIds
     * @return int[]
     */
    public function getOnlyExistingProductIds(array $productIds, int $domainId): array
    {
        $filterQuery = $this->filterQueryFactory->createOnlyExistingProductIdsFilterQuery($productIds, $domainId);
        $result = $this->client->search($filterQuery->getQuery());

        return $this->extractIdsFromFields($result);
    }

    /**
     * @param string[] $productUuids
     * @return int[]
     */
    public function getSellableProductIdsByUuids(array $productUuids): array
    {
        $filterQuery = $this->filterQueryFactory->createSellableProductIdsByProductUuidsFilter($productUuids);
        $result = $this->client->search($filterQuery->getQuery());

        return $this->extractIdsFromFields($result);
    }

    /**
     * @return int[]
     */
    protected function extractIdsFromFields(array $result): array
    {
        $ids = [];

        foreach ($result['hits']['hits'] as $hit) {
            foreach ($hit['fields']['id'] as $id) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    /**
     * @return int[]
     */
    public function getCategoryIdsForFilterData(ProductFilterData $productFilterData): array
    {
        $result = $this->client->search(
            $this->filterQueryFactory->createListableWithProductFilter($productFilterData)->setLimit(0)->getAggregationQueryForProductCountInCategories(),
        );

        return $this->extractCategoryIdsAggregation($result);
    }

    /**
     * @return int[]
     */
    protected function extractCategoryIdsAggregation(array $productCountAggregation): array
    {
        $result = [];

        foreach ($productCountAggregation['aggregations']['by_categories']['buckets'] as $categoryAggregation) {
            $result[] = $categoryAggregation['key'];
        }

        return $result;
    }
}
