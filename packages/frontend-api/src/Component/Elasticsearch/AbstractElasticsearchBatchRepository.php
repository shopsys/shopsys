<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Elasticsearch;

use Elasticsearch\Client;

abstract class AbstractElasticsearchBatchRepository
{
    public const string HITS_KEY = 'hits';

    public const string TOTALS_KEY = 'totals';

    public function __construct(
        protected readonly MultipleSearchQueryFactory $multipleSearchQueryFactory,
        protected readonly Client $client,
    ) {
    }

    abstract protected function getIndexName(): string;

    /**
     * @param array<string, mixed> $response
     * @return array<int, array<string, mixed>>
     */
    abstract protected function extractHits(array $response): array;

    /**
     * @param array<string, mixed> $response
     */
    abstract protected function extractTotalCount(array $response): int;

    /**
     * @param array<int|string, \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractFilterQuery|\Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery> $filterQueries
     * @return array{hits: array<int|string, array<int, array<string, mixed>>>, totals: array<int|string, int>}
     */
    public function getBatchedHitsAndTotalsByFilterQueries(array $filterQueries): array
    {
        $multipleSearchQuery = $this->multipleSearchQueryFactory->create($this->getIndexName(), $filterQueries);
        $result = $this->client->msearch($multipleSearchQuery->getQuery());
        $keys = array_keys($filterQueries);
        $hits = [];
        $totals = [];

        foreach ($result['responses'] as $index => $response) {
            $hits[$keys[$index]] = $this->extractHits($response);
            $totals[$keys[$index]] = $this->extractTotalCount($response);
        }

        return [
            static::HITS_KEY => $hits,
            static::TOTALS_KEY => $totals,
        ];
    }
}
