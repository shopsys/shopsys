<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article\Search;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ArticlesSearchQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Article\Search\ArticlesSearchResultsProviderResolver $articlesSearchResultsProviderResolver
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly ArticlesSearchResultsProviderResolver $articlesSearchResultsProviderResolver,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return array
     */
    public function articlesSearchQuery(Argument $argument): array
    {
        $articlesSearchResultsProvider = $this->articlesSearchResultsProviderResolver->getSearchResultsProviderByDomainIdAndEntityName($this->domain->getId(), 'article');

        return $articlesSearchResultsProvider->getArticlesSearchResults($argument);
    }
}
