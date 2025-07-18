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

                // === CONNECTION INITIALIZATION FIX - DISABLED FOR DOCKER HEALTH CHECK TEST ===
                // Temporary workaround replaced with Docker Compose health check solution
                // $connection->getDatabase();
                // $connection->getHost();
                error_log("🔍 [PROMOTED_DIAG] Testing Docker health check solution - workaround disabled");

                // === TRANSACTION STATE ANALYSIS ===
                error_log("🔍 [PROMOTED_DIAG] === TRANSACTION STATE ===");
                try {
                    $transactionLevel = $connection->getTransactionNestingLevel();
                    error_log("🔍 [PROMOTED_DIAG] Transaction nesting level: " . $transactionLevel);

                    $inTransaction = $connection->isTransactionActive();
                    error_log("🔍 [PROMOTED_DIAG] In active transaction: " . ($inTransaction ? 'YES' : 'NO'));
                } catch (\Exception $e) {
                    error_log("🔍 [PROMOTED_DIAG] Transaction state check failed: " . $e->getMessage());
                }

                // === SCHEMA AND SEARCH PATH ANALYSIS ===
                // COMMENTED OUT - Testing if schema queries cause connection warming
                // error_log("🔍 [PROMOTED_DIAG] === SCHEMA ANALYSIS ===");
                // try {
                //     $schemaResult = $connection->executeQuery("SELECT current_schema()");
                //     $currentSchema = $schemaResult->fetchOne();
                //     error_log("🔍 [PROMOTED_DIAG] Current schema: " . $currentSchema);
                //
                //     $searchPathResult = $connection->executeQuery("SHOW search_path");
                //     $searchPath = $searchPathResult->fetchOne();
                //     error_log("🔍 [PROMOTED_DIAG] Search path: " . $searchPath);
                // } catch (\Exception $e) {
                //     error_log("🔍 [PROMOTED_DIAG] Schema check failed: " . $e->getMessage());
                // }

                // === ENTITY MANAGER STATE ANALYSIS ===
                error_log("🔍 [PROMOTED_DIAG] === ENTITY MANAGER STATE ===");
                try {
                    $uow = $this->entityManager->getUnitOfWork();
                    $identityMapSize = count($uow->getIdentityMap());
                    error_log("🔍 [PROMOTED_DIAG] Identity map size: " . $identityMapSize);

                    $isOpen = $this->entityManager->isOpen();
                    error_log("🔍 [PROMOTED_DIAG] Entity manager open: " . ($isOpen ? 'YES' : 'NO'));
                } catch (\Exception $e) {
                    error_log("🔍 [PROMOTED_DIAG] Entity manager state check failed: " . $e->getMessage());
                }

                // === ENHANCED RAW SQL DIAGNOSTIC COMPARISON ===
                error_log("🔍 [PROMOTED_DIAG] === RAW SQL COMPARISON ===");

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
                error_log("🔍 [PROMOTED_DIAG] Domain ID used: " . $domainConfig->getId());

                // === TABLE EXISTENCE AND PERMISSION CHECK ===
                // COMMENTED OUT - Testing if table verification queries cause connection warming
                // error_log("🔍 [PROMOTED_DIAG] === TABLE VERIFICATION ===");
                // try {
                //     $tableCheckSql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'categories_top'";
                //     $tableResult = $connection->executeQuery($tableCheckSql);
                //     $tableExists = $tableResult->fetchOne();
                //     error_log("🔍 [PROMOTED_DIAG] categories_top table exists: " . ($tableExists > 0 ? 'YES' : 'NO'));
                //
                //     if ($tableExists > 0) {
                //         $totalRowsSql = "SELECT COUNT(*) FROM categories_top WHERE domain_id = :domainId";
                //         $totalResult = $connection->executeQuery($totalRowsSql, ['domainId' => $domainConfig->getId()]);
                //         $totalRows = $totalResult->fetchOne();
                //         error_log("🔍 [PROMOTED_DIAG] Total rows in categories_top for domain: " . $totalRows);
                //     }
                // } catch (\Exception $e) {
                //     error_log("🔍 [PROMOTED_DIAG] Table verification failed: " . $e->getMessage());
                // }

                // === CRITICAL ANALYSIS ===
                if (count($rawRows) > 0) {
                    error_log("🚨 [PROMOTED_CRITICAL] Raw SQL has data but ORM returns empty!");
                    error_log("🚨 [PROMOTED_CRITICAL] This confirms ORM connection/state issue!");
                    error_log("🔍 [PROMOTED_DIAG] First raw record: " . json_encode($rawRows[0]));
                } else {
                    error_log("🔍 [PROMOTED_DIAG] Both raw SQL and ORM return empty - data issue confirmed");
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
