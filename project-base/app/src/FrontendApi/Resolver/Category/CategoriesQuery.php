<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Category;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoriesQuery as BaseCategoriesQuery;

class CategoriesQuery extends BaseCategoriesQuery
{
    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        Domain $domain,
    ) {
        parent::__construct(
            $categoryFacade,
            $domain,
        );
    }

    /**
     * @return mixed[]
     */
    public function categoriesQuery(): array
    {
        /** @var \App\Model\Category\Category $rootCategory */
        $rootCategory = $this->categoryFacade->getRootCategory();

        return $this->categoryFacade->getAllVisibleChildrenByCategoryAndDomainConfig( // @phpstan-ignore-line
            $rootCategory,
            $this->domain->getCurrentDomainConfig(),
        );
    }
}
