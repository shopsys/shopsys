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
        private readonly Domain $domain,
    ) {
    }

    /**
     * @return \App\Model\Category\Category[]
     */
    public function promotedCategoriesQuery(): array
    {
        error_log("🔍 [PromotedCategoriesQuery] Starting query execution");
        $domainConfig = $this->domain->getCurrentDomainConfig();
        error_log("🔍 [PromotedCategoriesQuery] Current domain config retrieved");
        
        $result = $this->promotedCategoryFacade->getVisiblePromotedCategoriesOnDomain($domainConfig);
        
        error_log("🔍 [PromotedCategoriesQuery] Final result count: " . count($result));
        return $result;
    }
}
