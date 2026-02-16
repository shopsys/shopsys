<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Blog\Article\Elasticsearch;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchNoResultException;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchDataFetcher;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchRepository;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Blog\Article\Exception\BlogArticleNotFoundException;

class BlogArticleElasticsearchRepositoryTest extends TestCase
{
    private BlogArticleElasticsearchRepository $blogArticleElasticsearchRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        /** @var \Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\FilterQueryFactory|\PHPUnit\Framework\MockObject\Stub $filterQueryFactoryStub */
        $filterQueryFactoryStub = $this->createStub(FilterQueryFactory::class);
        /** @var \Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchDataFetcher|\PHPUnit\Framework\MockObject\Stub $blogArticleElasticsearchDataFetcherStub */
        $blogArticleElasticsearchDataFetcherStub = $this->createStub(BlogArticleElasticsearchDataFetcher::class);
        $blogArticleElasticsearchDataFetcherStub
            ->method('getSingleResult')->willThrowException(new ElasticsearchNoResultException());
        $this->blogArticleElasticsearchRepository = new BlogArticleElasticsearchRepository(
            $filterQueryFactoryStub,
            $blogArticleElasticsearchDataFetcherStub,
            new TransformStringHelper(),
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
