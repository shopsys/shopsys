<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Brand\Search;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class BrandSearchQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Brand\Search\BrandSearchResultsProviderResolver $brandSearchResultsProviderResolver
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly BrandSearchResultsProviderResolver $brandSearchResultsProviderResolver,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \GraphQL\Executor\Promise\Promise|\Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function brandSearchQuery(Argument $argument): Promise|array
    {
        $brandSearchResultsProvider = $this->brandSearchResultsProviderResolver->getSearchResultsProviderByDomainIdAndEntityName($this->domain->getId(), 'brand');

        return $brandSearchResultsProvider->getBrandSearchResults($argument);
    }
}
