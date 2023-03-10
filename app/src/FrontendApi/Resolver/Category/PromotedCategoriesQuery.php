<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Category;

use App\FrontendApi\Resolver\Category\PromotedCategory\PromotedCategoryFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class PromotedCategoriesQuery extends AbstractQuery
{
    /**
     * @param \App\FrontendApi\Resolver\Category\PromotedCategory\PromotedCategoryFacade $promotedCategoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly PromotedCategoryFacade $promotedCategoryFacade,
        private readonly Domain $domain
    ) {
    }

    /**
     * @return \App\Model\Category\Category[]
     */
    public function promotedCategoriesQuery(): array
    {
        return $this->promotedCategoryFacade->getVisiblePromotedCategoriesOnDomain($this->domain->getCurrentDomainConfig());
    }
}
