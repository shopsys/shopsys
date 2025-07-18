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
        // === CONNECTION STATUS LOGGING ===
        $connection = $this->categoryRepository->getEntityManager()->getConnection();
        error_log("🔍 [DB_CONN] Connection established: " . ($connection->isConnected() ? 'YES' : 'NO'));
        
        try {
            $connection->executeQuery("SELECT 1");
            error_log("🔍 [DB_PING] Connection test successful: YES");
        } catch (\Exception $e) {
            error_log("🔍 [DB_PING] Connection test failed: " . $e->getMessage());
        }
        
        // === CONNECTION DETAILS ===
        error_log("🔍 [DB_INFO] Database name: " . $connection->getDatabase());
        error_log("🔍 [DB_INFO] Host: " . $connection->getHost());
        error_log("🔍 [DB_INFO] Port: " . $connection->getPort());
        
        // === TRANSACTION STATE ===
        error_log("🔍 [DB_TRANS] Transaction active: " . ($connection->isTransactionActive() ? 'YES' : 'NO'));
        error_log("🔍 [DB_TRANS] Transaction nesting level: " . $connection->getTransactionNestingLevel());
        
        // === EXISTING DOMAIN LOGGING ===
        error_log("🔍 [DOMAIN] Domain: {$domainConfig->getName()} (ID: {$domainConfig->getId()})");
        error_log("🔍 [DOMAIN] Locale: {$domainConfig->getLocale()}");
        error_log("🔍 [DOMAIN] URL: {$domainConfig->getUrl()}");
        
        $queryBuilder = $this->categoryRepository->getAllVisibleByDomainIdQueryBuilder($domainConfig->getId());
        
        // === ORM QUERY SQL GENERATION LOGGING ===
        error_log("🔍 [ORM] Base query builder created");
        
        $finalQueryBuilder = $queryBuilder
            ->addSelect('ct, cd')
            ->join(TopCategory::class, 'tc', Join::WITH, 'tc.category = c AND tc.domainId = :domainId')
            ->join('c.translations', 'ct', Join::WITH, 'ct.locale = :locale')
            ->setParameter('locale', $domainConfig->getLocale())
            ->orderBy('tc.position');
        
        $query = $finalQueryBuilder->getQuery();
        
        // === ACTUAL SQL LOGGING ===
        error_log("🔍 [SQL] Generated SQL: " . $query->getSQL());
        error_log("🔍 [SQL] Parameters: " . json_encode($query->getParameters()));
        
        // === QUERY EXECUTION TIMING ===
        $startTime = microtime(true);
        
        try {
            $result = $query->getResult();
            $executionTime = (microtime(true) - $startTime) * 1000;
            
            error_log("🔍 [TIMING] Query execution time: {$executionTime}ms");
            error_log("🔍 [RESULT] Query returned: " . count($result) . " records");
            
            if (empty($result)) {
                error_log("⚠️ [ISSUE] EMPTY RESULT - This is the issue!");
                
                // === ADDITIONAL DIAGNOSTICS FOR EMPTY RESULTS ===
                error_log("🔍 [DIAG] Testing raw SQL equivalent...");
                
                $rawSql = "SELECT COUNT(*) FROM categories_top ct 
                          INNER JOIN categories c ON ct.category_id = c.id 
                          WHERE ct.domain_id = :domainId";
                
                $rawResult = $connection->executeQuery($rawSql, ['domainId' => $domainConfig->getId()]);
                $rawCount = $rawResult->fetchOne();
                
                error_log("🔍 [DIAG] Raw SQL count: " . $rawCount);
                
                if ($rawCount > 0) {
                    error_log("🚨 [CRITICAL] Raw SQL has data but ORM returns empty!");
                }
            }
            
            return $result;
            
        } catch (\Exception $e) {
            $executionTime = (microtime(true) - $startTime) * 1000;
            error_log("🔍 [TIMING] Query execution time (failed): {$executionTime}ms");
            error_log("🚨 [ERROR] Query failed: " . $e->getMessage());
            error_log("🚨 [ERROR] Stack trace: " . $e->getTraceAsString());
            
            return [];
        }
    }
}
