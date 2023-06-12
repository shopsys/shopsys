<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use App\Component\Elasticsearch\MultipleSearchQueryFactory;
use App\Model\Product\Filter\ProductFilterData;
use Doctrine\ORM\QueryBuilder;
use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository as BaseProductElasticsearchRepository;

/**
 * @property \App\Model\Product\Search\ProductElasticsearchConverter $productElasticsearchConverter
 * @property \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
 * @method \Shopsys\FrameworkBundle\Model\Product\Search\ProductIdsResult getSortedProductIdsByFilterQuery(\App\Model\Product\Search\FilterQuery $filterQuery)
 * @method \Shopsys\FrameworkBundle\Model\Product\Search\ProductsResult getSortedProductsResultByFilterQuery(\App\Model\Product\Search\FilterQuery $filterQuery)
 * @method int getProductsCountByFilterQuery(\App\Model\Product\Search\FilterQuery $filterQuery)
 * @method array getProductsByFilterQuery(\App\Model\Product\Search\FilterQuery $filterQuery)
 */
class ProductElasticsearchRepository extends BaseProductElasticsearchRepository
{
    public const PRODUCTS_KEY = 'products';

    public const TOTALS_KEY = 'totals';

    /**
     * @param \Elasticsearch\Client $client
     * @param \App\Model\Product\Search\ProductElasticsearchConverter $productElasticsearchConverter
     * @param \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \App\Component\Elasticsearch\MultipleSearchQueryFactory $multipleSearchQueryFactory
     */
    public function __construct(
        Client $client,
        ProductElasticsearchConverter $productElasticsearchConverter,
        FilterQueryFactory $filterQueryFactory,
        IndexDefinitionLoader $indexDefinitionLoader,
        private MultipleSearchQueryFactory $multipleSearchQueryFactory,
    ) {
        parent::__construct($client, $productElasticsearchConverter, $filterQueryFactory, $indexDefinitionLoader);
    }

    /**
     * {@inheritdoc}
     */
    protected function extractTotalCount(array $result): int
    {
        return (int)$result['hits']['total']['value'];
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $productQueryBuilder
     * @param string|null $searchText
     */
    public function filterBySearchText(QueryBuilder $productQueryBuilder, $searchText)
    {
        $productIds = $this->getFoundProductIds($productQueryBuilder, $searchText);

        if (count($productIds) > 0) {
            $productQueryBuilder->andWhere('p.id IN (:productIds)')
                ->orWhere('p.mainVariant IN (:productIds)')
                ->setParameter('productIds', $productIds);
        } else {
            $productQueryBuilder->andWhere('TRUE = FALSE');
        }
    }

    /**
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
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
     * @param array $productCountAggregation
     * @return int[]
     */
    private function extractCategoryIdsAggregation(array $productCountAggregation): array
    {
        $result = [];
        foreach ($productCountAggregation['aggregations']['by_categories']['buckets'] as $categoryAggregation) {
            $result[] = $categoryAggregation['key'];
        }

        return $result;
    }

    /**
     * @param \App\Model\Product\Search\FilterQuery[] $filterQueries
     * @return array
     */
    public function getBatchedProductsAndTotalsByFilterQueries(array $filterQueries): array
    {
        $mSearchQuery = $this->multipleSearchQueryFactory->create(ProductIndex::getName(), $filterQueries);
        $result = $this->client->msearch($mSearchQuery->getQuery());

        $keys = array_keys($filterQueries);
        $products = [];
        $totals = [];
        foreach ($result['responses'] as $index => $response) {
            $products[$keys[$index]] = $this->extractHits($response);
            $totals[$keys[$index]] = $this->extractTotalCount($response);
        }

        return [
            self::PRODUCTS_KEY => $products,
            self::TOTALS_KEY => $totals,
        ];
    }
}
