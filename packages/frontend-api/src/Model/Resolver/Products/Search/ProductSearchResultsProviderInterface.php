<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\Search;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection;
use Shopsys\FrontendApiBundle\Model\Resolver\Search\SearchResultsProviderInterface;

interface ProductSearchResultsProviderInterface extends SearchResultsProviderInterface
{
    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection|\GraphQL\Executor\Promise\Promise
     */
    public function getProductsSearchResults(
        Argument $argument,
    ): ProductConnection|Promise;
}
