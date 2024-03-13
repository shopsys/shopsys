<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Category\Search;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Component\Validation\PageSizeValidator;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class CategoriesSearchQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Category\Search\CategoriesSearchResultsProviderResolver $categoriesSearchResultsProviderResolver
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly CategoriesSearchResultsProviderResolver $categoriesSearchResultsProviderResolver,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object
     */
    public function categoriesSearchQuery(Argument $argument)
    {
        PageSizeValidator::checkMaxPageSize($argument);
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $categoriesSearchResultsProvider = $this->categoriesSearchResultsProviderResolver->getSearchResultsProviderByDomainIdAndEntityName($this->domain->getId(), 'category');

        return $categoriesSearchResultsProvider->getCategoriesSearchResults($argument);
    }
}
