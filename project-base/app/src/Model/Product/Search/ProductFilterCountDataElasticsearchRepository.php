<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository as BaseProductFilterCountDataElasticsearchRepository;

/**
 * @property \App\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer
 * @method __construct(\Shopsys\FrameworkBundle\Component\Elasticsearch\MultipleSearchFacade $multipleSearchFacade, \App\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer, \Shopsys\FrameworkBundle\Model\Product\Search\AggregationResultToProductFilterCountDataTransformer $aggregationResultToCountDataTransformer)
 * @method \App\Model\Product\Search\FilterQuery addParametersToQueryIfRequested(\App\Model\Product\Search\FilterQuery $filterQuery, \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataRequest $request)
 * @method array<string, array{index: string, body: array<string, mixed>}> createParametersPlusNumbersQueriesIndexedByQueryName(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $plusParametersQuery)
 */
class ProductFilterCountDataElasticsearchRepository extends BaseProductFilterCountDataElasticsearchRepository
{
}
