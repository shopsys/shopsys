<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use App\Model\Product\Filter\ProductFilterData;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository as BaseProductElasticsearchRepository;

/**
 * @property \App\Model\Product\Search\ProductElasticsearchConverter $productElasticsearchConverter
 * @property \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
 * @method \Shopsys\FrameworkBundle\Model\Product\Search\ProductIdsResult getSortedProductIdsByFilterQuery(\App\Model\Product\Search\FilterQuery $filterQuery)
 * @method \Shopsys\FrameworkBundle\Model\Product\Search\ProductsResult getSortedProductsResultByFilterQuery(\App\Model\Product\Search\FilterQuery $filterQuery)
 * @method int getProductsCountByFilterQuery(\App\Model\Product\Search\FilterQuery $filterQuery)
 * @method array getProductsByFilterQuery(\App\Model\Product\Search\FilterQuery $filterQuery)
 * @method __construct(\Elasticsearch\Client $client, \App\Model\Product\Search\ProductElasticsearchConverter $productElasticsearchConverter, \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory, \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader)
 */
class ProductElasticsearchRepository extends BaseProductElasticsearchRepository
{
    /**
     * {@inheritdoc}
     */
    public function extractTotalCount(array $result): int
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
}
