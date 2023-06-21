<?php

declare(strict_types=1);

namespace Tests\App\Unit\Model\Blog\Article\Elasticsearch;

use App\Component\Elasticsearch\NoResultException;
use App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchDataFetcher;
use App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchRepository;
use App\Model\Blog\Article\Elasticsearch\FilterQueryFactory;
use App\Model\Blog\Article\Exception\BlogArticleNotFoundException;
use PHPUnit\Framework\TestCase;

class BlogArticleElasticsearchRepositoryTest extends TestCase
{
    private BlogArticleElasticsearchRepository $blogArticleElasticsearchRepository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var \App\Model\Blog\Article\Elasticsearch\FilterQueryFactory|\PHPUnit\Framework\MockObject\MockObject $filterQueryFactoryMock */
        $filterQueryFactoryMock = $this->createMock(FilterQueryFactory::class);
        /** @var \App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchDataFetcher|\PHPUnit\Framework\MockObject\MockObject $blogArticleElasticsearchDataFetcherMock */
        $blogArticleElasticsearchDataFetcherMock = $this->createMock(BlogArticleElasticsearchDataFetcher::class);
        $blogArticleElasticsearchDataFetcherMock
            ->method('getSingleResult')->willThrowException(new NoResultException());
        $this->blogArticleElasticsearchRepository = new BlogArticleElasticsearchRepository(
            $filterQueryFactoryMock,
            $blogArticleElasticsearchDataFetcherMock,
        );
        $this->expectException(BlogArticleNotFoundException::class);
    }

    public function testGetByWrongUuidThrowsException()
    {
        $this->blogArticleElasticsearchRepository->getByUuid('123e4567-e89b-12d3-a456-426614174000');
    }

    public function testGetByWrongSlugThrowsException()
    {
        $this->blogArticleElasticsearchRepository->getBySlug('wrong-slug');
    }
}
