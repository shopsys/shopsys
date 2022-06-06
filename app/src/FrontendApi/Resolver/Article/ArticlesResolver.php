<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Article;

use App\FrontendApi\Component\Validation\PageSizeValidator;
use App\Model\Article\Elasticsearch\ArticleElasticsearchFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

/**
 * @method \App\Model\Article\Article[] getArticlesList(int $domainId, int $limit, int $offset, string|null $placement)
 */
class ArticlesResolver implements ResolverInterface, AliasedInterface
{
    private const DEFAULT_FIRST_LIMIT = 10;

    /**
     * @var \App\Model\Article\Elasticsearch\ArticleElasticsearchFacade
     */
    private ArticleElasticsearchFacade $articleElasticsearchFacade;

    /**
     * @param \App\Model\Article\Elasticsearch\ArticleElasticsearchFacade $articleElasticsearchFacade
     */
    public function __construct(ArticleElasticsearchFacade $articleElasticsearchFacade)
    {
        $this->articleElasticsearchFacade = $articleElasticsearchFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param string[] $placements
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object
     */
    public function resolve(Argument $argument, array $placements)
    {
        PageSizeValidator::checkMaxPageSize($argument);
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $paginator = new Paginator(function ($offset, $limit) use ($placements) {
            return $this->articleElasticsearchFacade->getAllArticles($offset, $limit, $placements);
        });

        return $paginator->auto($argument, $this->articleElasticsearchFacade->getAllArticlesTotalCount($placements));
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

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolve' => 'articles',
        ];
    }
}
