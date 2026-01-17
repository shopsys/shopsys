<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Category\Search;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Shopsys\FrontendApiBundle\Model\Resolver\Search\SearchResultsProviderInterface;

interface CategoriesSearchResultsProviderInterface extends SearchResultsProviderInterface
{
    public function getCategoriesSearchResults(
        Argument $argument,
    ): Promise|ConnectionInterface;
}
