<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Category\PromotedCategory;

use App\Model\Category\CategoryRepository;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategory;

class PromotedCategoryRepository
{
    /**
     * @param \App\Model\Category\CategoryRepository $categoryRepository
     */
    public function __construct(private CategoryRepository $categoryRepository)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Category\Category[]
     */
    public function getVisiblePromotedCategoriesOnDomain(DomainConfig $domainConfig): array
    {
        // === SIMPLIFIED LOGGING FOR NOW ===
        error_log("🔍 [PROMOTED] Domain: {$domainConfig->getName()} (ID: {$domainConfig->getId()})");
        error_log("🔍 [PROMOTED] Locale: {$domainConfig->getLocale()}");
        error_log("🔍 [PROMOTED] URL: {$domainConfig->getUrl()}");
        
        $queryBuilder = $this->categoryRepository->getAllVisibleByDomainIdQueryBuilder($domainConfig->getId());
        
        error_log("🔍 [PROMOTED] Base query builder created");
        
        $finalQueryBuilder = $queryBuilder
            ->addSelect('ct, cd')
            ->join(TopCategory::class, 'tc', Join::WITH, 'tc.category = c AND tc.domainId = :domainId')
            ->join('c.translations', 'ct', Join::WITH, 'ct.locale = :locale')
            ->setParameter('locale', $domainConfig->getLocale())
            ->orderBy('tc.position');
        
        $query = $finalQueryBuilder->getQuery();
        
        error_log("🔍 [PROMOTED] Generated SQL: " . $query->getSQL());
        error_log("🔍 [PROMOTED] Parameters: " . json_encode($query->getParameters()));
        
        $startTime = microtime(true);
        
        try {
            $result = $query->getResult();
            $executionTime = (microtime(true) - $startTime) * 1000;
            
            error_log("🔍 [PROMOTED] Query execution time: {$executionTime}ms");
            error_log("🔍 [PROMOTED] Query returned: " . count($result) . " records");
            
            if (empty($result)) {
                error_log("⚠️ [PROMOTED] EMPTY RESULT - This is the issue!");
            }
            
            return $result;
            
        } catch (\Exception $e) {
            $executionTime = (microtime(true) - $startTime) * 1000;
            error_log("🔍 [PROMOTED] Query execution time (failed): {$executionTime}ms");
            error_log("🚨 [PROMOTED] Query failed: " . $e->getMessage());
            error_log("🚨 [PROMOTED] Stack trace: " . $e->getTraceAsString());
            
            return [];
        }
    }
}
