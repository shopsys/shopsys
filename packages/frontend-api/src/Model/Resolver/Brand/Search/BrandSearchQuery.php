<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Brand\Search;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class BrandSearchQuery extends AbstractQuery
{
    public function __construct(
        protected readonly BrandSearchResultsProviderResolver $brandSearchResultsProviderResolver,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \GraphQL\Executor\Promise\Promise|\Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function brandSearchQuery(Argument $argument): Promise|array
    {
        $brandSearchResultsProvider = $this->brandSearchResultsProviderResolver->getSearchResultsProviderByDomainIdAndEntityName($this->domain->getId(), 'brand');

        return $brandSearchResultsProvider->getBrandSearchResults($argument);
    }
}
