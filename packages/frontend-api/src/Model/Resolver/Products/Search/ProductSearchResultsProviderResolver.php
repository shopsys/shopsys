<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\Search;

use Shopsys\FrontendApiBundle\Model\Resolver\Search\SearchResultsProviderResolver;

/**
 * @method \Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\ProductSearchResultsProviderInterface getSearchResultsProviderByDomainIdAndEntityName(int $domainId, string $searchedEntityName)
 */
class ProductSearchResultsProviderResolver extends SearchResultsProviderResolver
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\ProductSearchResultsProviderInterface[] $productSearchResultsProviders
     */
    public function __construct(
        protected readonly iterable $productSearchResultsProviders,
    ) {
        parent::__construct($productSearchResultsProviders);
    }

    /**
     * @return string
     */
    protected function getSearchResultsProviderInterface(): string
    {
        return ProductSearchResultsProviderInterface::class;
    }
}
