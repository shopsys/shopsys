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
        // === DATABASE CONNECTION STATUS LOGGING ===
        $baseQueryBuilder = $this->categoryRepository->getAllVisibleByDomainIdQueryBuilder($domainConfig->getId());
        $connection = $baseQueryBuilder->getEntityManager()->getConnection();
        
        error_log("🔍 [PROMOTED_CONN] Connection established: " . ($connection->isConnected() ? 'YES' : 'NO'));
        
        try {
            $pingResult = $connection->executeQuery("SELECT 1");
            error_log("🔍 [PROMOTED_PING] Connection test successful: YES");
        } catch (\Exception $e) {
            error_log("🔍 [PROMOTED_PING] Connection test failed: " . $e->getMessage());
        }
        
        error_log("🔍 [PROMOTED] Domain: {$domainConfig->getName()} (ID: {$domainConfig->getId()})");
        error_log("🔍 [PROMOTED] Locale: {$domainConfig->getLocale()}");
        error_log("🔍 [PROMOTED] URL: {$domainConfig->getUrl()}");
        
        $queryBuilder = $baseQueryBuilder;
        
        error_log("🔍 [PROMOTED] Base query builder created");
        
        $finalQueryBuilder = $queryBuilder
            ->addSelect('ct, cd')
            ->join(TopCategory::class, 'tc', Join::WITH, 'tc.category = c AND tc.domainId = :domainId')
            ->join('c.translations', 'ct', Join::WITH, 'ct.locale = :locale')
            ->setParameter('locale', $domainConfig->getLocale())
            ->orderBy('tc.position');
        
        $query = $finalQueryBuilder->getQuery();
        
        // === ORM SQL GENERATION LOGGING ===
        error_log("🔍 [PROMOTED_SQL] Generated SQL: " . $query->getSQL());
        error_log("🔍 [PROMOTED_SQL] Parameters: " . json_encode($query->getParameters()->toArray()));
        
        $startTime = microtime(true);
        
        try {
            $result = $query->getResult();
            $executionTime = (microtime(true) - $startTime) * 1000;
            
            error_log("🔍 [PROMOTED_TIMING] Query execution time: {$executionTime}ms");
            error_log("🔍 [PROMOTED_RESULT] Query returned: " . count($result) . " records");
            
            if (empty($result)) {
                error_log("⚠️ [PROMOTED_ISSUE] EMPTY RESULT - This is the issue!");
                
                // === RAW SQL DIAGNOSTIC COMPARISON ===
                error_log("🔍 [PROMOTED_DIAG] Testing raw SQL equivalent...");
                
                $rawSql = "SELECT tc.category_id as top_category_id, c.id as category_id 
                          FROM categories_top tc 
                          JOIN categories c ON tc.category_id = c.id 
                          JOIN category_domains cd ON c.id = cd.category_id 
                          WHERE tc.domain_id = :domainId 
                          AND cd.domain_id = :domainId 
                          AND cd.visible = true 
                          AND c.parent_id IS NOT NULL 
                          ORDER BY tc.position";
                
                $rawResult = $connection->executeQuery($rawSql, [
                    'domainId' => $domainConfig->getId(),
                ]);
                $rawRows = $rawResult->fetchAllAssociative();
                
                error_log("🔍 [PROMOTED_DIAG] Raw SQL returned: " . count($rawRows) . " records");
                
                if (count($rawRows) > 0) {
                    error_log("🚨 [PROMOTED_CRITICAL] Raw SQL has data but ORM returns empty!");
                    error_log("🔍 [PROMOTED_DIAG] First raw record: " . json_encode($rawRows[0]));
                } else {
                    error_log("🔍 [PROMOTED_DIAG] Raw SQL also returns empty - no data exists");
                }
            }
            
            return $result;
            
        } catch (\Exception $e) {
            $executionTime = (microtime(true) - $startTime) * 1000;
            error_log("🔍 [PROMOTED_TIMING] Query execution time (failed): {$executionTime}ms");
            error_log("🚨 [PROMOTED_ERROR] Query failed: " . $e->getMessage());
            error_log("🚨 [PROMOTED_ERROR] Stack trace: " . $e->getTraceAsString());
            
            return [];
        }
    }
}
