<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\Search;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ProductSearchQuery extends AbstractQuery
{
    public function __construct(
        protected readonly ProductSearchResultsProviderResolver $productSearchResultsProviderResolver,
        protected readonly Domain $domain,
    ) {
    }

    public function productsSearchQuery(Argument $argument): ProductConnection|Promise
    {
        $this->pageSizeValidator->checkMaxPageSize($argument);
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $productSearchResultsProvider = $this->productSearchResultsProviderResolver->getSearchResultsProviderByDomainIdAndEntityName($this->domain->getId(), 'product');

        return $productSearchResultsProvider->getProductsSearchResults($argument);
    }
}
