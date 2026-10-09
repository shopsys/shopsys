<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use Override;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository as BaseProductElasticsearchRepository;

/**
 * @property \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchConverter $productElasticsearchConverter
 * @property \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
 * @method \Shopsys\FrameworkBundle\Model\Product\Search\ProductsResult getSortedProductsResultByFilterQuery(\App\Model\Product\Search\FilterQuery $filterQuery)
 * @method int getProductsCountByFilterQuery(\App\Model\Product\Search\FilterQuery $filterQuery)
 * @method array getProductsByFilterQuery(\App\Model\Product\Search\FilterQuery $filterQuery)
 * @method __construct(\Elasticsearch\Client $client, \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchConverter $productElasticsearchConverter, \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory)
 */
class ProductElasticsearchRepository extends BaseProductElasticsearchRepository
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function extractTotalCount(array $result): int
    {
        return (int)$result['hits']['total']['value'];
    }
}
