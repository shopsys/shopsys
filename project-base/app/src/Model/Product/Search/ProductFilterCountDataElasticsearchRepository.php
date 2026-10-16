<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository as BaseProductFilterCountDataElasticsearchRepository;

/**
 * @property \App\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer
 * @method __construct(\Shopsys\FrameworkBundle\Component\Elasticsearch\MultipleSearchRepository $multipleSearchRepository, \App\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer, \Shopsys\FrameworkBundle\Model\Product\Search\AggregationResultToProductFilterCountDataTransformer $aggregationResultToCountDataTransformer)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData getProductFilterCountDataInSearch(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $baseFilterQuery)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData getProductFilterCountDataInCategory(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $baseFilterQuery)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData getProductFilterCountData(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $baseFilterQuery, bool $withParameters)
 * @method array<string, array{index: string, body: array<string, mixed>}> createSearchQueriesIndexedByQueryName(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $baseFilterQuery, bool $withParameters)
 * @method array{index: string, body: array<string, mixed>} createAbsoluteNumbersQuery(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $baseFilterQuery, bool $withParameters)
 * @method array<string, array{index: string, body: array<string, mixed>}> createParametersPlusNumbersQueriesIndexedByQueryName(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $plusParametersQuery)
 */
class ProductFilterCountDataElasticsearchRepository extends BaseProductFilterCountDataElasticsearchRepository
{
}
