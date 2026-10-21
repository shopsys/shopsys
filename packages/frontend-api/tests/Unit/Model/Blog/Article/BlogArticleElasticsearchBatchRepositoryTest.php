<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Blog\Article;

use Elasticsearch\Client;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchDataFetcher;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\FilterQuery;
use Shopsys\FrontendApiBundle\Component\Elasticsearch\MultipleSearchQuery;
use Shopsys\FrontendApiBundle\Component\Elasticsearch\MultipleSearchQueryFactory;
use Shopsys\FrontendApiBundle\Model\Blog\Article\BlogArticleElasticsearchBatchRepository;

class BlogArticleElasticsearchBatchRepositoryTest extends TestCase
{
    public function testResponsesAreMappedBackToFilterQueryKeys(): void
    {
        $tipsFilterQuery = $this->createStub(FilterQuery::class);
        $newsFilterQuery = $this->createStub(FilterQuery::class);
        $filterQueries = [
            'tips-batch' => $tipsFilterQuery,
            'news-batch' => $newsFilterQuery,
        ];
        $multipleSearchQueryBody = [
            'index' => 'blog_article_1',
            'body' => [],
        ];
        $tipsResponse = ['hits' => ['total' => ['value' => 1], 'hits' => [['_id' => '1', '_source' => []]]]];
        $newsResponse = ['hits' => ['total' => ['value' => 7], 'hits' => [['_id' => '2', '_source' => []]]]];
        $tipsArticles = [['id' => 1]];
        $newsArticles = [['id' => 2]];

        $multipleSearchQuery = $this->createStub(MultipleSearchQuery::class);
        $multipleSearchQuery->method('getQuery')->willReturn($multipleSearchQueryBody);

        $multipleSearchQueryFactory = $this->createMock(MultipleSearchQueryFactory::class);
        $multipleSearchQueryFactory->expects($this->once())
            ->method('create')
            ->with('blog_article', $filterQueries)
            ->willReturn($multipleSearchQuery);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('msearch')
            ->with($multipleSearchQueryBody)
            ->willReturn(['responses' => [$tipsResponse, $newsResponse]]);

        $blogArticleElasticsearchDataFetcher = $this->createStub(BlogArticleElasticsearchDataFetcher::class);
        $blogArticleElasticsearchDataFetcher->method('extractHits')
            ->willReturnMap([
                [$tipsResponse, $tipsArticles],
                [$newsResponse, $newsArticles],
            ]);
        $blogArticleElasticsearchDataFetcher->method('extractTotalCount')
            ->willReturnMap([
                [$tipsResponse, 1],
                [$newsResponse, 7],
            ]);

        $repository = new BlogArticleElasticsearchBatchRepository(
            $multipleSearchQueryFactory,
            $client,
            $blogArticleElasticsearchDataFetcher,
        );

        $this->assertSame(
            [
                BlogArticleElasticsearchBatchRepository::HITS_KEY => ['tips-batch' => $tipsArticles, 'news-batch' => $newsArticles],
                BlogArticleElasticsearchBatchRepository::TOTALS_KEY => ['tips-batch' => 1, 'news-batch' => 7],
            ],
            $repository->getBatchedHitsAndTotalsByFilterQueries($filterQueries),
        );
    }
}
