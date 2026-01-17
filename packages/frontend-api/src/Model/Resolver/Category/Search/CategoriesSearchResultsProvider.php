<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Category\Search;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Category\CategoryFacade;

class CategoriesSearchResultsProvider implements CategoriesSearchResultsProviderInterface
{
    public function __construct(
        protected readonly CategoryFacade $categoryFacade,
        protected readonly Domain $domain,
    ) {
    }

    #[Override]
    public function getCategoriesSearchResults(
        Argument $argument,
    ): Promise|ConnectionInterface {
        $searchText = $argument['searchInput']['search'] ?? '';

        $paginator = new Paginator(function ($offset, $limit) use ($searchText) {
            return $this->categoryFacade->getVisibleCategoriesBySearchText(
                $searchText,
                $this->domain->getLocale(),
                $this->domain->getId(),
                $offset,
                $limit,
            );
        });

        return $paginator->auto(
            $argument,
            $this->categoryFacade->getVisibleCategoriesBySearchTextCount(
                $searchText,
                $this->domain->getLocale(),
                $this->domain->getId(),
            ),
        );
    }

    #[Override]
    public function isEnabledOnDomain(int $domainId): bool
    {
        return true;
    }
}
