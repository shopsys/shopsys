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
        // === CONNECTION INITIALIZATION SOLUTION ===
        $baseQueryBuilder = $this->categoryRepository->getAllVisibleByDomainIdQueryBuilder($domainConfig->getId());
        $connection = $baseQueryBuilder->getEntityManager()->getConnection();

        error_log("🔍 [PROMOTED_CONN] Connection established: " . ($connection->isConnected() ? 'YES' : 'NO'));

        // CONNECTION INITIALIZATION - Force full Doctrine DBAL initialization
        error_log("🔍 [PROMOTED_INIT] === FORCING CONNECTION INITIALIZATION ===");
        try {
            $dbName = $connection->getDatabase();
            $params = $connection->getParams();
            $host = $params['host'] ?? 'unknown';
            error_log("🔍 [PROMOTED_INIT] Database metadata accessed: {$dbName} @ {$host}");
            error_log("🔍 [PROMOTED_INIT] Connection should now be fully initialized");
        } catch (\Exception $e) {
            error_log("🔍 [PROMOTED_INIT] Connection initialization failed: " . $e->getMessage());
        }
        error_log("🔍 [PROMOTED_TEST] Testing connection initialization solution");

        // === PHP ENVIRONMENT ANALYSIS ===
        error_log("🔍 [PROMOTED_ENV] === PHP ENVIRONMENT DIAGNOSTICS ===");
        error_log("🔍 [PROMOTED_ENV] PHP Version: " . PHP_VERSION);
        error_log("🔍 [PROMOTED_ENV] SAPI: " . php_sapi_name());
        error_log("🔍 [PROMOTED_ENV] PDO PostgreSQL: " . (extension_loaded('pdo_pgsql') ? 'YES' : 'NO'));
        error_log("🔍 [PROMOTED_ENV] PostgreSQL: " . (extension_loaded('pgsql') ? 'YES' : 'NO'));
        error_log("🔍 [PROMOTED_ENV] JSON: " . (extension_loaded('json') ? 'YES' : 'NO'));
        error_log("🔍 [PROMOTED_ENV] Memory limit: " . ini_get('memory_limit'));
        error_log("🔍 [PROMOTED_ENV] Error reporting: " . error_reporting());
        error_log("🔍 [PROMOTED_ENV] Timezone: " . date_default_timezone_get());
        
        $doctrineVersion = class_exists('\Doctrine\DBAL\Version') ? \Doctrine\DBAL\Version::VERSION : 'unknown';
        error_log("🔍 [PROMOTED_ENV] Doctrine DBAL Version: " . $doctrineVersion);

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

        // === PARAMETER VALUE DIAGNOSTICS ===
        $domainId = $domainConfig->getId();
        $locale = $domainConfig->getLocale();
        
        error_log("🔍 [PROMOTED_PARAMS] === PARAMETER BINDING ANALYSIS ===");
        error_log("🔍 [PROMOTED_PARAMS] Raw parameter values BEFORE setParameter:");
        error_log("🔍 [PROMOTED_PARAMS] - domainId: " . var_export($domainId, true) . " (type: " . gettype($domainId) . ")");
        error_log("🔍 [PROMOTED_PARAMS] - locale: " . var_export($locale, true) . " (type: " . gettype($locale) . ")");

        $finalQueryBuilder = $queryBuilder
            ->addSelect('ct, cd')
            ->join(TopCategory::class, 'tc', Join::WITH, 'tc.category = c AND tc.domainId = :domainId')
            ->join('c.translations', 'ct', Join::WITH, 'ct.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('tc.position');

        // === PARAMETER BINDING VERIFICATION ===
        $setParams = $finalQueryBuilder->getParameters();
        error_log("🔍 [PROMOTED_PARAMS] Query builder parameters AFTER setParameter:");
        foreach ($setParams as $key => $value) {
            error_log("🔍 [PROMOTED_PARAMS] - {$key}: " . var_export($value, true) . " (type: " . gettype($value) . ")");
        }

        $query = $finalQueryBuilder->getQuery();

        // === ORM SQL GENERATION LOGGING ===
        error_log("🔍 [PROMOTED_SQL] Generated SQL: " . $query->getSQL());
        
        $queryParams = $query->getParameters()->toArray();
        error_log("🔍 [PROMOTED_SQL] Query parameters from getParameters(): " . json_encode($queryParams));
        error_log("🔍 [PROMOTED_PARAMS] Query parameter details:");
        foreach ($queryParams as $key => $value) {
            error_log("🔍 [PROMOTED_PARAMS] - Query param {$key}: " . var_export($value, true) . " (type: " . gettype($value) . ")");
        }

        $startTime = microtime(true);

        try {
            $result = $query->getResult();
            $executionTime = (microtime(true) - $startTime) * 1000;

            error_log("🔍 [PROMOTED_TIMING] Query execution time: {$executionTime}ms");
            error_log("🔍 [PROMOTED_RESULT] Query returned: " . count($result) . " records");

            if (empty($result)) {
                error_log("⚠️ [PROMOTED_ISSUE] EMPTY RESULT - Connection initialization should have prevented this!");
                error_log("🔍 [PROMOTED_TEST] Testing direct parameter binding to bypass ORM layer...");
                
                // === DIRECT PARAMETER BINDING TEST ===
                error_log("🔍 [PROMOTED_DIRECT] === BYPASSING ORM PARAMETER BINDING ===");
                
                try {
                    // Test 1: Direct SQL with string substitution (not recommended but good for testing)
                    $directSql = "SELECT COUNT(*) FROM categories c INNER JOIN category_domains cd ON c.id = cd.category_id INNER JOIN categories_top ct ON ct.category_id = c.id WHERE cd.domain_id = {$domainId} AND cd.visible = true AND ct.domain_id = {$domainId} AND c.parent_id IS NOT NULL";
                    $directResult = $connection->executeQuery($directSql);
                    $directCount = $directResult->fetchOne();
                    error_log("🔍 [PROMOTED_DIRECT] Direct SQL string substitution count: " . $directCount);
                    
                    // Test 2: DBAL Connection with proper parameter binding  
                    $dbalSql = "SELECT COUNT(*) FROM categories c INNER JOIN category_domains cd ON c.id = cd.category_id INNER JOIN categories_top ct ON ct.category_id = c.id WHERE cd.domain_id = ? AND cd.visible = true AND ct.domain_id = ? AND c.parent_id IS NOT NULL";
                    $dbalResult = $connection->executeQuery($dbalSql, [$domainId, $domainId]);
                    $dbalCount = $dbalResult->fetchOne();
                    error_log("🔍 [PROMOTED_DIRECT] DBAL connection parameter binding count: " . $dbalCount);
                    
                    // Test 3: DBAL Connection with named parameters
                    $namedSql = "SELECT COUNT(*) FROM categories c INNER JOIN category_domains cd ON c.id = cd.category_id INNER JOIN categories_top ct ON ct.category_id = c.id WHERE cd.domain_id = :domainId AND cd.visible = true AND ct.domain_id = :domainId AND c.parent_id IS NOT NULL";
                    $namedResult = $connection->executeQuery($namedSql, [
                        'domainId' => $domainId,
                    ]);
                    $namedCount = $namedResult->fetchOne();
                    error_log("🔍 [PROMOTED_DIRECT] DBAL named parameter binding count: " . $namedCount);
                    
                    error_log("🔍 [PROMOTED_DIRECT] Results comparison - Direct: {$directCount}, DBAL positional: {$dbalCount}, DBAL named: {$namedCount}");
                    
                    // === PDO LEVEL TEST ===
                    error_log("🔍 [PROMOTED_PDO] === TESTING PDO LEVEL DIRECTLY ===");
                    
                    try {
                        // Get the underlying PDO connection from DBAL
                        $pdo = $connection->getNativeConnection();
                        
                        if ($pdo instanceof \PDO) {
                            error_log("🔍 [PROMOTED_PDO] PDO connection obtained successfully");
                            
                            // Test PDO with prepared statements
                            $pdoSql = "SELECT COUNT(*) FROM categories c INNER JOIN category_domains cd ON c.id = cd.category_id INNER JOIN categories_top ct ON ct.category_id = c.id WHERE cd.domain_id = ? AND cd.visible = true AND ct.domain_id = ? AND c.parent_id IS NOT NULL";
                            $pdoStmt = $pdo->prepare($pdoSql);
                            $pdoStmt->execute([$domainId, $domainId]);
                            $pdoCount = $pdoStmt->fetchColumn();
                            error_log("🔍 [PROMOTED_PDO] PDO prepared statement count: " . $pdoCount);
                            
                            // Test PDO with named parameters
                            $pdoNamedSql = "SELECT COUNT(*) FROM categories c INNER JOIN category_domains cd ON c.id = cd.category_id INNER JOIN categories_top ct ON ct.category_id = c.id WHERE cd.domain_id = :domainId AND cd.visible = true AND ct.domain_id = :domainId2 AND c.parent_id IS NOT NULL";
                            $pdoNamedStmt = $pdo->prepare($pdoNamedSql);
                            $pdoNamedStmt->execute([
                                ':domainId' => $domainId,
                                ':domainId2' => $domainId,
                            ]);
                            $pdoNamedCount = $pdoNamedStmt->fetchColumn();
                            error_log("🔍 [PROMOTED_PDO] PDO named parameters count: " . $pdoNamedCount);
                            
                            error_log("🔍 [PROMOTED_PDO] PDO Results - Positional: {$pdoCount}, Named: {$pdoNamedCount}");
                        } else {
                            error_log("🔍 [PROMOTED_PDO] Could not get PDO connection - type: " . gettype($pdo));
                        }
                    } catch (\Exception $e) {
                        error_log("🔍 [PROMOTED_PDO] PDO test failed: " . $e->getMessage());
                    }
                    
                } catch (\Exception $e) {
                    error_log("🔍 [PROMOTED_DIRECT] Direct binding test failed: " . $e->getMessage());
                }
            } else {
                error_log("✅ [PROMOTED_SUCCESS] Connection initialization solution CONFIRMED - query returned results!");
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
