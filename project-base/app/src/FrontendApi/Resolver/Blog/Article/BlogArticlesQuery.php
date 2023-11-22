<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Blog\Article;

use App\FrontendApi\Component\Validation\PageSizeValidator;
use App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade;
use App\Model\Blog\Category\BlogCategory;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class BlogArticlesQuery extends AbstractQuery
{
    private const DEFAULT_FIRST_LIMIT = 10;

    /**
     * @param \App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade
     */
    public function __construct(private readonly BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade)
    {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return object|\Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface
     */
    public function blogArticlesQuery(Argument $argument): object
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
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @return object|\Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface
     */
    public function blogArticleByCategoryQuery(Argument $argument, BlogCategory $blogCategory): object
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
    private function setDefaultFirstOffsetIfNecessary(Argument $argument): void
    {
        if ($argument->offsetExists('first') === false
            && $argument->offsetExists('last') === false
        ) {
            $argument->offsetSet('first', self::DEFAULT_FIRST_LIMIT);
        }
    }
}
