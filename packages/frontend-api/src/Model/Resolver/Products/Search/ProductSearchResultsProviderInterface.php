<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\Search;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection;

interface ProductSearchResultsProviderInterface
{
    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection|\GraphQL\Executor\Promise\Promise
     */
    public function getProductsSearchResults(
        Argument $argument,
        ProductFilterData $productFilterData,
    ): ProductConnection|Promise;

    /**
     * @param int $domainId
     * @return bool
     */
    public function isEnabledOnDomain(int $domainId): bool;
}
