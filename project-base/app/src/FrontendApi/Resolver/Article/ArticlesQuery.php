<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Article;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleElasticsearchFacade;
use Shopsys\FrontendApiBundle\Component\Validation\PageSizeValidator;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

/**
 * @method \App\Model\Article\Article[] getArticlesList(int $domainId, int $limit, int $offset, string|null $placement)
 */
class ArticlesQuery extends AbstractQuery
{
    private const DEFAULT_FIRST_LIMIT = 10;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleElasticsearchFacade $articleElasticsearchFacade
     */
    public function __construct(private readonly ArticleElasticsearchFacade $articleElasticsearchFacade)
    {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param string[] $placements
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object
     */
    public function articlesQuery(Argument $argument, array $placements)
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
            $argument->offsetSet('first', self::DEFAULT_FIRST_LIMIT);
        }
    }
}
