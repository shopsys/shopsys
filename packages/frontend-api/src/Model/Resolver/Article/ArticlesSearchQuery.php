<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\CombinedArticle\CombinedArticleElasticsearchFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ArticlesSearchQuery extends AbstractQuery
{
    protected const ARTICLE_SEARCH_LIMIT = 50;

    /**
     * @param \Shopsys\FrameworkBundle\Model\CombinedArticle\CombinedArticleElasticsearchFacade $combinedArticleElasticsearchFacade
     */
    public function __construct(
        protected readonly CombinedArticleElasticsearchFacade $combinedArticleElasticsearchFacade,
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
            static::ARTICLE_SEARCH_LIMIT,
        );
    }
}
