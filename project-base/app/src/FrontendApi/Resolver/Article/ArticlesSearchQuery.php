<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Article;

use App\Model\Article\CombinedArticleElasticsearchFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ArticlesSearchQuery extends AbstractQuery
{
    private const ARTICLE_SEARCH_LIMIT = 50;

    /**
     * @param \App\Model\Article\CombinedArticleElasticsearchFacade $combinedArticleElasticsearchFacade
     */
    public function __construct(
        private readonly CombinedArticleElasticsearchFacade $combinedArticleElasticsearchFacade,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return array
     */
    public function articlesSearchQuery(Argument $argument): array
    {
        return $this->combinedArticleElasticsearchFacade->getArticlesBySearchText(
            $argument['search'] ?? '',
            self::ARTICLE_SEARCH_LIMIT,
        );
    }
}
