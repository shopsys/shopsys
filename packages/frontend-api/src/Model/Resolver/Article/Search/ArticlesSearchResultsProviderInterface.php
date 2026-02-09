<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article\Search;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Model\Resolver\Search\SearchResultsProviderInterface;

interface ArticlesSearchResultsProviderInterface extends SearchResultsProviderInterface
{
    public function getArticlesSearchResults(
        Argument $argument,
    ): Promise|array;
}
