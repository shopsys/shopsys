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
        // === SERVICE INITIALIZATION LOGGING ===
        error_log("🔍 [SERVICE] Starting query execution");
        error_log("🔍 [TEST] PromotedCategoriesQuery::promotedCategoriesQuery() was called!");
        error_log("🔍 [SERVICE] EntityManager available: " . ($this->promotedCategoryFacade ? 'YES' : 'NO'));
        error_log("🔍 [SERVICE] Domain service available: " . ($this->domain ? 'YES' : 'NO'));
        
        // === TIMING MEASUREMENT ===
        $startTime = microtime(true);
        
        try {
            $domainConfig = $this->domain->getCurrentDomainConfig();
            $domainRetrievalTime = (microtime(true) - $startTime) * 1000;
            
            error_log("🔍 [TIMING] Domain config retrieval: {$domainRetrievalTime}ms");
            error_log("🔍 [SERVICE] Domain config retrieved successfully");
            
            $queryStartTime = microtime(true);
            $result = $this->promotedCategoryFacade->getVisiblePromotedCategoriesOnDomain($domainConfig);
            $queryExecutionTime = (microtime(true) - $queryStartTime) * 1000;
            
            error_log("🔍 [TIMING] Facade query execution: {$queryExecutionTime}ms");
            error_log("🔍 [PROMOTED_FINAL] Final result count: " . count($result));
            
            return $result;
            
        } catch (\Exception $e) {
            $totalTime = (microtime(true) - $startTime) * 1000;
            error_log("🔍 [TIMING] Total execution time (failed): {$totalTime}ms");
            error_log("🚨 [ERROR] Query failed: " . $e->getMessage());
            error_log("🚨 [ERROR] Stack trace: " . $e->getTraceAsString());
            
            return [];
        }
    }
}
