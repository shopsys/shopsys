<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository as BaseProductElasticsearchRepository;

/**
 * @property \App\Model\Product\Search\ProductElasticsearchConverter $productElasticsearchConverter
 * @property \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
 * @method \Shopsys\FrameworkBundle\Model\Product\Search\ProductIdsResult getSortedProductIdsByFilterQuery(\App\Model\Product\Search\FilterQuery $filterQuery)
 * @method \Shopsys\FrameworkBundle\Model\Product\Search\ProductsResult getSortedProductsResultByFilterQuery(\App\Model\Product\Search\FilterQuery $filterQuery)
 * @method int getProductsCountByFilterQuery(\App\Model\Product\Search\FilterQuery $filterQuery)
 * @method array getProductsByFilterQuery(\App\Model\Product\Search\FilterQuery $filterQuery)
 * @method __construct(\Elasticsearch\Client $client, \App\Model\Product\Search\ProductElasticsearchConverter $productElasticsearchConverter, \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory, \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader, \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache)
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
}
