<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleElasticsearchFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ArticlesQuery extends AbstractQuery
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ArticleElasticsearchFacade $articleElasticsearchFacade,
    ) {
    }

    /**
     * @param string[] $placements
     */
    public function articlesQuery(
        Argument $argument,
        array $placements,
    ): Connection|Promise {
        $this->pageSizeValidator->checkMaxPageSize($argument);
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $paginator = new Paginator(function ($offset, $limit) use ($placements) {
            return $this->articleElasticsearchFacade->getAllArticles($offset, $limit, $placements);
        });

        return $paginator->auto($argument, $this->articleElasticsearchFacade->getAllArticlesTotalCount($placements));
    }
}
