<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Article;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class BlogArticlesQuery extends AbstractQuery
{
    public function __construct(
        protected readonly BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade,
    ) {
    }

    /**
     * @return object|\Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface
     */
    public function blogArticlesQuery(Argument $argument)
    {
        $this->pageSizeValidator->checkMaxPageSize($argument);
        $onlyVisibleOnHomepage = $argument['onlyHomepageArticles'];

        $this->setDefaultFirstOffsetIfNecessary($argument);
        $paginator = new Paginator(function ($offset, $limit) use ($onlyVisibleOnHomepage) {
            return $this->blogArticleElasticsearchFacade->getAllBlogArticles($offset, $limit, $onlyVisibleOnHomepage);
        });

        return $paginator->auto($argument, $this->blogArticleElasticsearchFacade->getAllBlogArticlesTotalCount());
    }

    /**
     * @return object|\Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface
     */
    public function blogArticleByCategoryQuery(Argument $argument, BlogCategory $blogCategory)
    {
        $this->pageSizeValidator->checkMaxPageSize($argument);
        $onlyVisibleOnHomepage = $argument['onlyHomepageArticles'];

        $this->setDefaultFirstOffsetIfNecessary($argument);
        $paginator = new Paginator(function ($offset, $limit) use ($blogCategory, $onlyVisibleOnHomepage) {
            return $this->blogArticleElasticsearchFacade->getByBlogCategory($blogCategory, $offset, $limit, $onlyVisibleOnHomepage);
        });

        return $paginator->auto($argument, $this->blogArticleElasticsearchFacade->getByBlogCategoryTotalCount($blogCategory));
    }
}
