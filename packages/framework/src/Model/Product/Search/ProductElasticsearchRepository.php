<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

class ProductElasticsearchRepository
{
    public function __construct(
        protected readonly Client $client,
        protected readonly ProductElasticsearchConverter $productElasticsearchConverter,
        protected readonly FilterQueryFactory $filterQueryFactory,
    ) {
    }

    public function getSortedProductsResultByFilterQuery(FilterQuery $filterQuery): ProductsResult
    {
        $result = $this->client->search($this->excludeReviewsFromSource($filterQuery->getQuery()));

        return new ProductsResult($this->extractTotalCount($result), $this->extractHits($result));
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
        $result = $this->client->search($this->excludeReviewsFromSource($filterQuery->getQuery()));

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
            $query['body']['_source'] = [
                'excludes' => [ProductExportFieldProvider::REVIEWS],
            ];
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
