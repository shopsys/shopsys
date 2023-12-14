<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleElasticsearchFacade;
use Shopsys\FrontendApiBundle\Component\Validation\PageSizeValidator;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ArticlesQuery extends AbstractQuery
{
    protected const DEFAULT_FIRST_LIMIT = 10;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleElasticsearchFacade $articleElasticsearchFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ArticleElasticsearchFacade $articleElasticsearchFacade,
    ) {
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
    protected function setDefaultFirstOffsetIfNecessary(Argument $argument): void
    {
        if ($argument->offsetExists('first') === false
            && $argument->offsetExists('last') === false
        ) {
            $argument->offsetSet('first', static::DEFAULT_FIRST_LIMIT);
        }
    }
}
