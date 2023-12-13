<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Blog\Article\Elasticsearch;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchNoResultException;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchDataFetcher;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchRepository;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Blog\Article\Exception\BlogArticleNotFoundException;

class BlogArticleElasticsearchRepositoryTest extends TestCase
{
    private BlogArticleElasticsearchRepository $blogArticleElasticsearchRepository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var \Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\FilterQueryFactory|\PHPUnit\Framework\MockObject\MockObject $filterQueryFactoryMock */
        $filterQueryFactoryMock = $this->createMock(FilterQueryFactory::class);
        /** @var \Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchDataFetcher|\PHPUnit\Framework\MockObject\MockObject $blogArticleElasticsearchDataFetcherMock */
        $blogArticleElasticsearchDataFetcherMock = $this->createMock(BlogArticleElasticsearchDataFetcher::class);
        $blogArticleElasticsearchDataFetcherMock
            ->method('getSingleResult')->willThrowException(new ElasticsearchNoResultException());
        $this->blogArticleElasticsearchRepository = new BlogArticleElasticsearchRepository(
            $filterQueryFactoryMock,
            $blogArticleElasticsearchDataFetcherMock,
        );
        $this->expectException(BlogArticleNotFoundException::class);
    }

    public function testGetByWrongUuidThrowsException(): void
    {
        $this->blogArticleElasticsearchRepository->getByUuid('123e4567-e89b-12d3-a456-426614174000');
    }

    public function testGetByWrongSlugThrowsException(): void
    {
        $this->blogArticleElasticsearchRepository->getBySlug('wrong-slug');
    }
}
