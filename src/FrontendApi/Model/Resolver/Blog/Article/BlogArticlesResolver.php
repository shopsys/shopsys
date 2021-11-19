<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Blog\Article;

use App\FrontendApi\Component\Validation\PageSizeValidator;
use App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade;
use App\Model\Blog\Category\BlogCategory;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

class BlogArticlesResolver implements ResolverInterface, AliasedInterface
{
    private const DEFAULT_FIRST_LIMIT = 10;

    /**
     * @var \App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade
     */
    private BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade;

    /**
     * @param \App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade
     */
    public function __construct(BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade)
    {
        $this->blogArticleElasticsearchFacade = $blogArticleElasticsearchFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return object|\Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface
     */
    public function resolveAll(Argument $argument)
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
    public function resolveByCategory(Argument $argument, BlogCategory $blogCategory)
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
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolveAll' => 'blogArticles',
            'resolveByCategory' => 'blogArticlesByCategory',
        ];
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     */
    private function setDefaultFirstOffsetIfNecessary(Argument $argument): void
    {
        if ($argument->offsetExists('first') === false
            && $argument->offsetExists('last') === false
        ) {
            $argument->offsetSet('first', static::DEFAULT_FIRST_LIMIT);
        }
    }
}
