<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\Search;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection;
use Shopsys\FrontendApiBundle\Model\Resolver\Search\SearchResultsProviderInterface;

interface ProductSearchResultsProviderInterface extends SearchResultsProviderInterface
{
    public function getProductsSearchResults(
        Argument $argument,
    ): ProductConnection|Promise;
}
