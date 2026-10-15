<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Blog\Article;

use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrontendApiBundle\Component\Validation\PageSizeValidator;
use Shopsys\FrontendApiBundle\Model\Blog\Article\BlogArticlesByBlogCategoryBatchLoadData;
use Shopsys\FrontendApiBundle\Model\Blog\Article\BlogArticlesByBlogCategoryBatchLoadDataFactory;
use Shopsys\FrontendApiBundle\Model\Blog\Article\BlogArticlesByBlogCategoryBatchLoadResult;
use Shopsys\FrontendApiBundle\Model\Blog\Article\BlogArticlesQuery;

class BlogArticlesQueryTest extends TestCase
{
    private const int TOTAL_COUNT = 7;

    public function testForwardPaginationUsesBatchLoadedTotalCount(): void
    {
        $blogCategory = $this->createStub(BlogCategory::class);
        $blogArticleElasticsearchFacade = $this->createMock(BlogArticleElasticsearchFacade::class);
        $blogArticleElasticsearchFacade->expects($this->never())->method('getByBlogCategoryTotalCount');

        $blogArticlesQuery = $this->createBlogArticlesQuery(
            $blogArticleElasticsearchFacade,
            $this->createBatchLoaderMock(new BlogArticlesByBlogCategoryBatchLoadData($blogCategory, 0, 3, false)),
        );

        $connection = new SyncPromiseAdapter()->wait($blogArticlesQuery->blogArticleByCategoryQuery(
            new Argument([
                'first' => 2,
                'onlyHomepageArticles' => false,
            ]),
            $blogCategory,
        ));

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertSame(self::TOTAL_COUNT, $connection->getTotalCount());
        $this->assertCount(2, $connection->getEdges());
        $this->assertTrue($connection->getPageInfo()->getHasNextPage());
    }

    public function testBackwardPaginationCountsTotalBeforeLoadingTheBatch(): void
    {
        $blogCategory = $this->createStub(BlogCategory::class);
        $blogArticleElasticsearchFacade = $this->createMock(BlogArticleElasticsearchFacade::class);
        $blogArticleElasticsearchFacade->expects($this->once())
            ->method('getByBlogCategoryTotalCount')
            ->with($blogCategory, true)
            ->willReturn(self::TOTAL_COUNT);

        $blogArticlesQuery = $this->createBlogArticlesQuery(
            $blogArticleElasticsearchFacade,
            $this->createBatchLoaderMock(new BlogArticlesByBlogCategoryBatchLoadData($blogCategory, 5, 2, true)),
        );

        $connection = new SyncPromiseAdapter()->wait($blogArticlesQuery->blogArticleByCategoryQuery(
            new Argument([
                'last' => 2,
                'onlyHomepageArticles' => true,
            ]),
            $blogCategory,
        ));

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertSame(self::TOTAL_COUNT, $connection->getTotalCount());
        $this->assertCount(2, $connection->getEdges());
        $this->assertTrue($connection->getPageInfo()->getHasPreviousPage());
    }

    private function createBatchLoaderMock(
        BlogArticlesByBlogCategoryBatchLoadData $expectedBatchLoadData,
    ): DataLoaderInterface|MockObject {
        $batchLoader = $this->createMock(DataLoaderInterface::class);
        $batchLoader->expects($this->once())
            ->method('load')
            ->with($this->equalTo($expectedBatchLoadData))
            ->willReturn(new SyncPromiseAdapter()->createFulfilled(new BlogArticlesByBlogCategoryBatchLoadResult(
                [['id' => 1], ['id' => 2], ['id' => 3]],
                self::TOTAL_COUNT,
            )));

        return $batchLoader;
    }

    private function createBlogArticlesQuery(
        BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade,
        DataLoaderInterface $blogArticlesByBlogCategoryBatchLoader,
    ): BlogArticlesQuery {
        $blogArticlesQuery = new BlogArticlesQuery(
            $blogArticleElasticsearchFacade,
            $blogArticlesByBlogCategoryBatchLoader,
            new BlogArticlesByBlogCategoryBatchLoadDataFactory(),
        );
        $blogArticlesQuery->autowirePageSizeValidator($this->createStub(PageSizeValidator::class));

        return $blogArticlesQuery;
    }
}
