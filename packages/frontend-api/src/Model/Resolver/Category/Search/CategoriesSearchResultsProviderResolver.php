<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Category\Search;

use Shopsys\FrontendApiBundle\Model\Resolver\Search\SearchResultsProviderResolver;

/**
 * @method \Shopsys\FrontendApiBundle\Model\Resolver\Category\Search\CategoriesSearchResultsProviderInterface getSearchResultsProviderByDomainIdAndEntityName(int $domainId, string $searchedEntityName)
 */
class CategoriesSearchResultsProviderResolver extends SearchResultsProviderResolver
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\ProductSearchResultsProviderInterface[] $categoriesSearchResultsProviders
     */
    public function __construct(
        protected readonly iterable $categoriesSearchResultsProviders,
    ) {
        parent::__construct($categoriesSearchResultsProviders);
    }

    /**
     * @return string
     */
    protected function getSearchResultsProviderInterface(): string
    {
        return CategoriesSearchResultsProviderInterface::class;
    }
}
