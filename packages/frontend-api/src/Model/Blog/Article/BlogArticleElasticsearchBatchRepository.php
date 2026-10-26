<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Article;

use Shopsys\FrameworkBundle\Component\Elasticsearch\MultipleSearchRepository;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchDataFetcher;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\FilterQuery;

class BlogArticleElasticsearchBatchRepository
{
    public const string HITS_KEY = 'hits';

    public const string TOTALS_KEY = 'totals';

    public function __construct(
        protected readonly MultipleSearchRepository $multipleSearchRepository,
        protected readonly BlogArticleElasticsearchDataFetcher $blogArticleElasticsearchDataFetcher,
    ) {
    }

    /**
     * @param array<int|string, \Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\FilterQuery> $filterQueries
     * @return array{hits: array<int|string, array<int, array<string, mixed>>>, totals: array<int|string, int>}
     */
    public function getBatchedHitsAndTotalsByFilterQueries(array $filterQueries): array
    {
        $responsesIndexedByKey = $this->multipleSearchRepository->searchIndexedByKey(array_map(
            static fn (FilterQuery $filterQuery): array => $filterQuery->getQuery(),
            $filterQueries,
        ));

        $hits = [];
        $totals = [];

        foreach ($responsesIndexedByKey as $key => $response) {
            $hits[$key] = $this->blogArticleElasticsearchDataFetcher->extractHits($response);
            $totals[$key] = $this->blogArticleElasticsearchDataFetcher->extractTotalCount($response);
        }

        return [
            self::HITS_KEY => $hits,
            self::TOTALS_KEY => $totals,
        ];
    }
}
