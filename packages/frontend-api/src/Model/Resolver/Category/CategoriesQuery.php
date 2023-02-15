<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Category;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class CategoriesQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly CategoryFacade $categoryFacade,
        protected readonly Domain $domain
    ) {
    }

    /**
     * @return array
     */
    public function categoriesQuery(): array
    {
        return $this->categoryFacade->getAllVisibleChildrenByCategoryAndDomainId(
            $this->categoryFacade->getRootCategory(),
            $this->domain->getId()
        );
    }
}
