<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\BatchLoad;

use Elasticsearch\Client;
use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\FrontendApiBundle\Component\Elasticsearch\AbstractElasticsearchBatchRepository;
use Shopsys\FrontendApiBundle\Component\Elasticsearch\MultipleSearchQueryFactory;

class ProductElasticsearchBatchRepository extends AbstractElasticsearchBatchRepository
{
    public const PRODUCTS_KEY = 'products';

    public function __construct(
        MultipleSearchQueryFactory $multipleSearchQueryFactory,
        Client $client,
        protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
    ) {
        parent::__construct($multipleSearchQueryFactory, $client);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery[] $filterQueries
     */
    public function getBatchedProductsAndTotalsByFilterQueries(array $filterQueries): array
    {
        $batchedHitsAndTotals = $this->getBatchedHitsAndTotalsByFilterQueries($filterQueries);

        return [
            static::PRODUCTS_KEY => $batchedHitsAndTotals[static::HITS_KEY],
            static::TOTALS_KEY => $batchedHitsAndTotals[static::TOTALS_KEY],
        ];
    }

    #[Override]
    protected function getIndexName(): string
    {
        return ExtendedClassNameResolver::resolve(ProductIndex::class)::getName();
    }

    #[Override]
    protected function extractHits(array $response): array
    {
        return $this->productElasticsearchRepository->extractHits($response);
    }

    #[Override]
    protected function extractTotalCount(array $response): int
    {
        return $this->productElasticsearchRepository->extractTotalCount($response);
    }
}
