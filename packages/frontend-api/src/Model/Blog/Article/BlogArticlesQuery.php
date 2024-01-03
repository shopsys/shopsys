<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Article;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrontendApiBundle\Component\Validation\PageSizeValidator;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class BlogArticlesQuery extends AbstractQuery
{
    protected const DEFAULT_FIRST_LIMIT = 10;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade
     */
    public function __construct(
        protected readonly BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return object|\Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface
     */
    public function blogArticlesQuery(Argument $argument)
    {
        PageSizeValidator::checkMaxPageSize($argument);
        $onlyVisibleOnHomepage = $argument['onlyHomepageArticles'];

        $this->setDefaultFirstOffsetIfNecessary($argument);
        $paginator = new Paginator(function ($offset, $limit) use ($onlyVisibleOnHomepage) {
            return $this->blogArticleElasticsearchFacade->getAllBlogArticles($offset, $limit, $onlyVisibleOnHomepage);
        });

        return $paginator->auto($argument, $this->blogArticleElasticsearchFacade->getAllBlogArticlesTotalCount());
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $blogCategory
     * @return object|\Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface
     */
    public function blogArticleByCategoryQuery(Argument $argument, BlogCategory $blogCategory)
    {
        PageSizeValidator::checkMaxPageSize($argument);
        $onlyVisibleOnHomepage = $argument['onlyHomepageArticles'];

        $this->setDefaultFirstOffsetIfNecessary($argument);
        $paginator = new Paginator(function ($offset, $limit) use ($blogCategory, $onlyVisibleOnHomepage) {
            return $this->blogArticleElasticsearchFacade->getByBlogCategory($blogCategory, $offset, $limit, $onlyVisibleOnHomepage);
        });

        return $paginator->auto($argument, $this->blogArticleElasticsearchFacade->getByBlogCategoryTotalCount($blogCategory));
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     */
    protected function setDefaultFirstOffsetIfNecessary(Argument $argument): void
    {
        if ($argument->offsetExists('first') === false
            && $argument->offsetExists('last') === false
        ) {
            $argument->offsetSet('first', static::DEFAULT_FIRST_LIMIT);
        }
    }
}
