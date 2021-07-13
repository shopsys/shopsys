<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Blog\Article;

use App\FrontendApi\Model\Blog\Article\BlogArticleElasticsearchFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

class BlogArticlesResolver implements ResolverInterface, AliasedInterface
{
    private const DEFAULT_FIRST_LIMIT = 10;

    /**
     * @var \App\FrontendApi\Model\Blog\Article\BlogArticleElasticsearchFacade
     */
    private BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade;

    /**
     * @param \App\FrontendApi\Model\Blog\Article\BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade
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
        $this->setDefaultFirstOffsetIfNecessary($argument);
        $paginator = new Paginator(function ($offset, $limit) {
            return $this->blogArticleElasticsearchFacade->getAllBlogArticles($offset, $limit);
        });

        return $paginator->auto($argument, $this->blogArticleElasticsearchFacade->getAllBlogArticlesTotalCount());
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolveAll' => 'blogArticles',
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
