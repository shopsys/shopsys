<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article\Search;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\CombinedArticle\CombinedArticleElasticsearchFacade;

class ArticlesSearchResultsProvider implements ArticlesSearchResultsProviderInterface
{
    public function __construct(
        protected readonly CombinedArticleElasticsearchFacade $combinedArticleElasticsearchFacade,
        protected readonly Domain $domain,
    ) {
    }

    #[Override]
    public function getArticlesSearchResults(
        Argument $argument,
    ): Promise|array {
        return $this->combinedArticleElasticsearchFacade->getArticlesBySearchText(
            $argument['searchInput']['search'] ?? '',
            $this->domain->getId(),
            ArticlesSearchQuery::ARTICLE_SEARCH_LIMIT,
        );
    }

    #[Override]
    public function isEnabledOnDomain(int $domainId): bool
    {
        return true;
    }
}
