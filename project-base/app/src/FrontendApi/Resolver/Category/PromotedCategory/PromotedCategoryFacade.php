<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Category\PromotedCategory;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class PromotedCategoryFacade
{
    /**
     * @var \App\FrontendApi\Resolver\Category\PromotedCategory\PromotedCategoryRepository
     */
    private PromotedCategoryRepository $promotedCategoryRepository;

    /**
     * @param \App\FrontendApi\Resolver\Category\PromotedCategory\PromotedCategoryRepository $promotedCategoryRepository
     */
    public function __construct(PromotedCategoryRepository $promotedCategoryRepository)
    {
        $this->promotedCategoryRepository = $promotedCategoryRepository;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Category\Category[]
     */
    public function getVisiblePromotedCategoriesOnDomain(DomainConfig $domainConfig): array
    {
        return $this->promotedCategoryRepository->getVisiblePromotedCategoriesOnDomain($domainConfig);
    }
}
