<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Category\PromotedCategory;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class PromotedCategoryFacade
{
    public function __construct(private PromotedCategoryRepository $promotedCategoryRepository)
    {
    }

    /**
     * @return \App\Model\Category\Category[]
     */
    public function getVisiblePromotedCategoriesOnDomain(DomainConfig $domainConfig): array
    {
        return $this->promotedCategoryRepository->getVisiblePromotedCategoriesOnDomain($domainConfig);
    }
}
