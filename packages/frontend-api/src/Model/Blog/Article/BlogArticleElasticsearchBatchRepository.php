<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Article;

use Elasticsearch\Client;
use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchDataFetcher;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleIndex;
use Shopsys\FrontendApiBundle\Component\Elasticsearch\AbstractElasticsearchBatchRepository;
use Shopsys\FrontendApiBundle\Component\Elasticsearch\MultipleSearchQueryFactory;

class BlogArticleElasticsearchBatchRepository extends AbstractElasticsearchBatchRepository
{
    public function __construct(
        MultipleSearchQueryFactory $multipleSearchQueryFactory,
        Client $client,
        protected readonly BlogArticleElasticsearchDataFetcher $blogArticleElasticsearchDataFetcher,
    ) {
        parent::__construct($multipleSearchQueryFactory, $client);
    }

    #[Override]
    protected function getIndexName(): string
    {
        return ExtendedClassNameResolver::resolve(BlogArticleIndex::class)::getName();
    }

    #[Override]
    protected function extractHits(array $response): array
    {
        return $this->blogArticleElasticsearchDataFetcher->extractHits($response);
    }

    #[Override]
    protected function extractTotalCount(array $response): int
    {
        return $this->blogArticleElasticsearchDataFetcher->extractTotalCount($response);
    }
}
