<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Article;

use App\Model\Article\CombinedArticleElasticsearchFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

class ArticlesSearchResolver implements QueryInterface, AliasedInterface
{
    private const ARTICLE_SEARCH_LIMIT = 50;

    /**
     * @var \App\Model\Article\CombinedArticleElasticsearchFacade
     */
    private CombinedArticleElasticsearchFacade $combinedArticleElasticsearchFacade;

    /**
     * @param \App\Model\Article\CombinedArticleElasticsearchFacade $combinedArticleElasticsearchFacade
     */
    public function __construct(CombinedArticleElasticsearchFacade $combinedArticleElasticsearchFacade)
    {
        $this->combinedArticleElasticsearchFacade = $combinedArticleElasticsearchFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return array
     */
    public function resolveArticlesSearch(Argument $argument): array
    {
        return $this->combinedArticleElasticsearchFacade->getArticlesBySearchText(
            $argument['search'] ?? '',
            self::ARTICLE_SEARCH_LIMIT
        );
    }

    /**
     * @return array
     */
    public static function getAliases(): array
    {
        return [
            'resolveArticlesSearch' => 'resolveArticlesSearch',
        ];
    }
}
