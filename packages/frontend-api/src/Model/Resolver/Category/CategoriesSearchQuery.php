<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Category;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Category\CategoryFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class CategoriesSearchQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly CategoryFacade $categoryFacade,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object
     */
    public function categoriesSearchQuery(Argument $argument)
    {
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $searchText = $argument['search'] ?? '';

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
}
