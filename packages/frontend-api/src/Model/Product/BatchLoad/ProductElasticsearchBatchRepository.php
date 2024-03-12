<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\BatchLoad;

use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\FrontendApiBundle\Component\Elasticsearch\MultipleSearchQueryFactory;

class ProductElasticsearchBatchRepository
{
    public const PRODUCTS_KEY = 'products';

    public const TOTALS_KEY = 'totals';

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Elasticsearch\MultipleSearchQueryFactory $multipleSearchQueryFactory
     * @param \Elasticsearch\Client $client
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
     */
    public function __construct(
        protected readonly MultipleSearchQueryFactory $multipleSearchQueryFactory,
        protected readonly Client $client,
        protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery[] $filterQueries
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
            $products[$keys[$index]] = $this->productElasticsearchRepository->extractHits($response);
            $totals[$keys[$index]] = $this->productElasticsearchRepository->extractTotalCount($response);
        }

        return [
            self::PRODUCTS_KEY => $products,
            self::TOTALS_KEY => $totals,
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery[] $filterQueries
     * @return int[]
     */
    public function getBatchedTotalsByFilterQueries(array $filterQueries, int $domainId): array
    {
        $mSearchQuery = $this->multipleSearchQueryFactory->createForDomain(ProductIndex::getName(), $filterQueries, $domainId);
        $result = $this->client->msearch($mSearchQuery->getQuery());
d(['msearchresult', $result]);
        $keys = array_keys($filterQueries);
        $totals = [];

        foreach ($result['responses'] as $index => $response) {
            $totals[$keys[$index]] = $this->productElasticsearchRepository->extractTotalCount($response);
        }

        return $totals;
    }
}
