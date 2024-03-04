<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article\Search;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\CombinedArticle\CombinedArticleElasticsearchFacade;

class ArticlesSearchResultsProvider implements ArticlesSearchResultsProviderInterface
{
    protected const int ARTICLE_SEARCH_LIMIT = 50;

    /**
     * @param \Shopsys\FrameworkBundle\Model\CombinedArticle\CombinedArticleElasticsearchFacade $combinedArticleElasticsearchFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly CombinedArticleElasticsearchFacade $combinedArticleElasticsearchFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \GraphQL\Executor\Promise\Promise|array
     */
    public function getArticlesSearchResults(
        Argument $argument,
    ): Promise|array {
        return $this->combinedArticleElasticsearchFacade->getArticlesBySearchText(
            $argument['search'] ?? '',
            $this->domain->getId(),
            static::ARTICLE_SEARCH_LIMIT,
        );
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isEnabledOnDomain(int $domainId): bool
    {
        return true;
    }
}
