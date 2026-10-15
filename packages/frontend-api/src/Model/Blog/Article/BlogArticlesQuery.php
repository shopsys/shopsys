<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Article;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class BlogArticlesQuery extends AbstractQuery
{
    public function __construct(
        protected readonly BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade,
        protected readonly DataLoaderInterface $blogArticlesByBlogCategoryBatchLoader,
        protected readonly BlogArticlesByBlogCategoryBatchLoadDataFactory $blogArticlesByBlogCategoryBatchLoadDataFactory,
    ) {
    }

    public function blogArticlesQuery(
        Argument $argument,
    ): Connection|Promise {
        $this->pageSizeValidator->checkMaxPageSize($argument);
        $onlyVisibleOnHomepage = $argument['onlyHomepageArticles'];

        $this->setDefaultFirstOffsetIfNecessary($argument);
        $paginator = new Paginator(function ($offset, $limit) use ($onlyVisibleOnHomepage) {
            return $this->blogArticleElasticsearchFacade->getAllBlogArticles($offset, $limit, $onlyVisibleOnHomepage);
        });

        return $paginator->auto($argument, $this->blogArticleElasticsearchFacade->getAllBlogArticlesTotalCount());
    }

    public function blogArticleByCategoryQuery(
        Argument $argument,
        BlogCategory $blogCategory,
    ): Promise {
        $this->pageSizeValidator->checkMaxPageSize($argument);
        $onlyVisibleOnHomepage = $argument['onlyHomepageArticles'];

        $this->setDefaultFirstOffsetIfNecessary($argument);
        $batchLoadedTotalCount = null;

        $paginator = new Paginator(
            function ($offset, $limit) use (&$batchLoadedTotalCount, $blogCategory, $onlyVisibleOnHomepage) {
                $batchLoadData = $this->blogArticlesByBlogCategoryBatchLoadDataFactory->create(
                    $blogCategory,
                    $offset,
                    $limit,
                    $onlyVisibleOnHomepage,
                );

                return $this->blogArticlesByBlogCategoryBatchLoader
                    ->load($batchLoadData)
                    ->then(function (BlogArticlesByBlogCategoryBatchLoadResult $result) use (&$batchLoadedTotalCount): array {
                        $batchLoadedTotalCount = $result->getTotalCount();

                        return $result->getBlogArticles();
                    });
            },
            Paginator::MODE_PROMISE,
        );

        return $paginator->auto($argument, function () use (&$batchLoadedTotalCount, $blogCategory, $onlyVisibleOnHomepage): int {
            return $this->getBatchLoadedOrCountedTotalCount($batchLoadedTotalCount, $blogCategory, $onlyVisibleOnHomepage);
        });
    }

    protected function getBatchLoadedOrCountedTotalCount(
        ?int $batchLoadedTotalCount,
        BlogCategory $blogCategory,
        bool $onlyVisibleOnHomepage,
    ): int {
        if ($batchLoadedTotalCount !== null) {
            return $batchLoadedTotalCount;
        }

        return $this->blogArticleElasticsearchFacade->getByBlogCategoryTotalCount($blogCategory, $onlyVisibleOnHomepage);
    }
}
