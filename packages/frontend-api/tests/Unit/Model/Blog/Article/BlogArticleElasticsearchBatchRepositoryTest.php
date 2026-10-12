<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Blog\Article;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Elasticsearch\MultipleSearchRepository;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchDataFetcher;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\FilterQuery;
use Shopsys\FrontendApiBundle\Model\Blog\Article\BlogArticleElasticsearchBatchRepository;

class BlogArticleElasticsearchBatchRepositoryTest extends TestCase
{
    public function testResponsesAreMappedBackToFilterQueryKeys(): void
    {
        $tipsQuery = [
            'index' => 'blog_article_1',
            'body' => [
                'from' => 0,
            ],
        ];
        $newsQuery = [
            'index' => 'blog_article_1',
            'body' => [
                'from' => 2,
            ],
        ];
        $tipsFilterQuery = $this->createStub(FilterQuery::class);
        $tipsFilterQuery->method('getQuery')->willReturn($tipsQuery);
        $newsFilterQuery = $this->createStub(FilterQuery::class);
        $newsFilterQuery->method('getQuery')->willReturn($newsQuery);
        $tipsResponse = [
            'hits' => [
                'total' => [
                    'value' => 1,
                ],
                'hits' => [['_id' => '1', '_source' => []]],
            ],
        ];
        $newsResponse = ['hits' => ['total' => ['value' => 7], 'hits' => [['_id' => '2', '_source' => []]]]];
        $tipsArticles = [['id' => 1]];
        $newsArticles = [['id' => 2]];

        $multipleSearchRepository = $this->createMock(MultipleSearchRepository::class);
        $multipleSearchRepository->expects($this->once())
            ->method('searchIndexedByKey')
            ->with(['tips-batch' => $tipsQuery, 'news-batch' => $newsQuery])
            ->willReturn(['tips-batch' => $tipsResponse, 'news-batch' => $newsResponse]);

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

        $repository = new BlogArticleElasticsearchBatchRepository($multipleSearchRepository, $blogArticleElasticsearchDataFetcher);

        $this->assertSame(
            [
                BlogArticleElasticsearchBatchRepository::HITS_KEY => ['tips-batch' => $tipsArticles, 'news-batch' => $newsArticles],
                BlogArticleElasticsearchBatchRepository::TOTALS_KEY => ['tips-batch' => 1, 'news-batch' => 7],
            ],
            $repository->getBatchedHitsAndTotalsByFilterQueries(['tips-batch' => $tipsFilterQuery, 'news-batch' => $newsFilterQuery]),
        );
    }
}
