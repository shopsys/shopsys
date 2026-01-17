<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Category\Search;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class CategoriesSearchQuery extends AbstractQuery
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly CategoriesSearchResultsProviderResolver $categoriesSearchResultsProviderResolver,
    ) {
    }

    public function categoriesSearchQuery(Argument $argument): Promise|ConnectionInterface
    {
        $this->pageSizeValidator->checkMaxPageSize($argument);
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $categoriesSearchResultsProvider = $this->categoriesSearchResultsProviderResolver->getSearchResultsProviderByDomainIdAndEntityName($this->domain->getId(), 'category');

        return $categoriesSearchResultsProvider->getCategoriesSearchResults($argument);
    }
}
