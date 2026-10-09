<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\BatchLoad;

use Shopsys\FrameworkBundle\Component\Elasticsearch\MultipleSearchFacade;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;

class ProductElasticsearchBatchRepository
{
    public const PRODUCTS_KEY = 'products';

    public const TOTALS_KEY = 'totals';

    public function __construct(
        protected readonly MultipleSearchFacade $multipleSearchFacade,
        protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery[] $filterQueries
     */
    public function getBatchedProductsAndTotalsByFilterQueries(array $filterQueries): array
    {
        $responsesIndexedByKey = $this->multipleSearchFacade->searchIndexedByKey(array_map(
            static fn (FilterQuery $filterQuery): array => $filterQuery->getQuery(),
            $filterQueries,
        ));

        $products = [];
        $totals = [];

        foreach ($responsesIndexedByKey as $key => $response) {
            $products[$key] = $this->productElasticsearchRepository->extractHits($response);
            $totals[$key] = $this->productElasticsearchRepository->extractTotalCount($response);
        }

        return [
            self::PRODUCTS_KEY => $products,
            self::TOTALS_KEY => $totals,
        ];
    }
}
