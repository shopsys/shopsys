<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Brand\Search;

use Override;
use Shopsys\FrontendApiBundle\Model\Resolver\Search\SearchResultsProviderResolver;

/**
 * @method \Shopsys\FrontendApiBundle\Model\Resolver\Brand\Search\BrandSearchResultsProviderInterface getSearchResultsProviderByDomainIdAndEntityName(int $domainId, string $searchedEntityName)
 */
class BrandSearchResultsProviderResolver extends SearchResultsProviderResolver
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Brand\Search\BrandSearchResultsProviderInterface[] $brandSearchResultsProviders
     */
    public function __construct(
        iterable $brandSearchResultsProviders,
    ) {
        parent::__construct($brandSearchResultsProviders);
    }

    #[Override]
    protected function getSearchResultsProviderInterface(): string
    {
        return BrandSearchResultsProviderInterface::class;
    }
}
