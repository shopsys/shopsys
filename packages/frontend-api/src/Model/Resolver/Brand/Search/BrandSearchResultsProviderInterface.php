<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Brand\Search;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Model\Resolver\Search\SearchResultsProviderInterface;

interface BrandSearchResultsProviderInterface extends SearchResultsProviderInterface
{
    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \GraphQL\Executor\Promise\Promise|array
     */
    public function getBrandSearchResults(
        Argument $argument,
    ): Promise|array;
}
