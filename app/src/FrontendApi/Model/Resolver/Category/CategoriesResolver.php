<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Category;

use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoriesResolver as BaseCategoriesResolver;

/**
 * @property \App\Model\Category\CategoryFacade $categoryFacade
 * @method __construct(\App\Model\Category\CategoryFacade $categoryFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain)
 */
class CategoriesResolver extends BaseCategoriesResolver
{
    /**
     * @return array
     */
    public function resolve(): array
    {
        return $this->categoryFacade->getAllVisibleChildrenByCategoryAndDomainConfig(
            $this->categoryFacade->getRootCategory(),
            $this->domain->getCurrentDomainConfig()
        );
    }
}
