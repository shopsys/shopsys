<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article\Search;

use Override;
use Shopsys\FrontendApiBundle\Model\Resolver\Search\SearchResultsProviderResolver;

/**
 * @method \Shopsys\FrontendApiBundle\Model\Resolver\Article\Search\ArticlesSearchResultsProviderInterface getSearchResultsProviderByDomainIdAndEntityName(int $domainId, string $searchedEntityName)
 */
class ArticlesSearchResultsProviderResolver extends SearchResultsProviderResolver
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Article\Search\ArticlesSearchResultsProviderInterface[] $articlesSearchResultsProviders
     */
    public function __construct(
        protected readonly iterable $articlesSearchResultsProviders,
    ) {
        parent::__construct($articlesSearchResultsProviders);
    }

    #[Override]
    protected function getSearchResultsProviderInterface(): string
    {
        return ArticlesSearchResultsProviderInterface::class;
    }
}
