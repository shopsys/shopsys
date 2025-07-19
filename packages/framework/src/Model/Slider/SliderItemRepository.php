<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Slider;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Slider\Exception\SliderItemNotFoundException;

class SliderItemRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getSliderItemRepository()
    {
        return $this->em->getRepository(SliderItem::class);
    }

    /**
     * @param int $sliderItemId
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem
     */
    public function getById($sliderItemId)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Slider\SliderItem|null $sliderItem */
        $sliderItem = $this->getSliderItemRepository()->find($sliderItemId);

        if ($sliderItem === null) {
            $message = 'Slider item with ID ' . $sliderItemId . ' not found.';

            throw new SliderItemNotFoundException($message);
        }

        return $sliderItem;
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem|null
     */
    public function findById($id)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Slider\SliderItem $sliderItem */
        $sliderItem = $this->getSliderItemRepository()->find($id);

        return $sliderItem;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem[]
     */
    public function getAll()
    {
        return $this->getSliderItemRepository()->findAll();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem[]
     */
    public function getAllVisibleByDomainId(int $domainId): array
    {
        // === CONNECTION INITIALIZATION SOLUTION ===
        $connection = $this->em->getConnection();
        error_log("🔍 [SLIDER_CONN] Connection established: " . ($connection->isConnected() ? 'YES' : 'NO'));

        // CONNECTION INITIALIZATION - Force full Doctrine DBAL initialization
        error_log("🔍 [SLIDER_INIT] === FORCING CONNECTION INITIALIZATION ===");
        try {
            $dbName = $connection->getDatabase();
            $params = $connection->getParams();
            $host = $params['host'] ?? 'unknown';
            error_log("🔍 [SLIDER_INIT] Database metadata accessed: {$dbName} @ {$host}");
            error_log("🔍 [SLIDER_INIT] Connection should now be fully initialized");
        } catch (\Exception $e) {
            error_log("🔍 [SLIDER_INIT] Connection initialization failed: " . $e->getMessage());
        }
        error_log("🔍 [SLIDER_TEST] Testing connection initialization solution");

        // === PHP ENVIRONMENT ANALYSIS ===
        error_log("🔍 [SLIDER_ENV] === PHP ENVIRONMENT DIAGNOSTICS ===");
        error_log("🔍 [SLIDER_ENV] PHP Version: " . PHP_VERSION);
        error_log("🔍 [SLIDER_ENV] SAPI: " . php_sapi_name());
        error_log("🔍 [SLIDER_ENV] PDO PostgreSQL: " . (extension_loaded('pdo_pgsql') ? 'YES' : 'NO'));
        error_log("🔍 [SLIDER_ENV] PostgreSQL: " . (extension_loaded('pgsql') ? 'YES' : 'NO'));
        error_log("🔍 [SLIDER_ENV] JSON: " . (extension_loaded('json') ? 'YES' : 'NO'));
        error_log("🔍 [SLIDER_ENV] Memory limit: " . ini_get('memory_limit'));
        error_log("🔍 [SLIDER_ENV] Error reporting: " . error_reporting());
        error_log("🔍 [SLIDER_ENV] Timezone: " . date_default_timezone_get());
        
        $doctrineVersion = class_exists('\Doctrine\DBAL\Version') ? \Doctrine\DBAL\Version::VERSION : 'unknown';
        error_log("🔍 [SLIDER_ENV] Doctrine DBAL Version: " . $doctrineVersion);

        try {
            $connection->executeQuery("SELECT 1");
            error_log("🔍 [SLIDER_PING] Connection test successful: YES");
        } catch (\Exception $e) {
            error_log("🔍 [SLIDER_PING] Connection test failed: " . $e->getMessage());
        }

        error_log("🔍 [SLIDER] Domain ID: {$domainId}");

        $dateToday = new DateTime();
        $dateToday = $dateToday->format('Y-m-d 00:00:00');

        error_log("🔍 [SLIDER] Date filter: {$dateToday}");
        error_log("🔍 [SLIDER] Current time: " . (new DateTime())->format('Y-m-d H:i:s'));

        $queryBuilder = $this->getSliderItemQueryBuilder()
            ->where('si.domainId = :domainId')
            ->andWhere('si.hidden = :hidden')
            ->andWhere('si.datetimeVisibleFrom is NULL or si.datetimeVisibleFrom <= :now')
            ->andWhere('si.datetimeVisibleTo is NULL or si.datetimeVisibleTo >= :now')
            ->orderBy('si.position')
            ->addOrderBy('si.id');

        // === PARAMETER VALUE DIAGNOSTICS ===
        $paramValues = [
            'domainId' => $domainId,
            'hidden' => false,
            'now' => $dateToday,
        ];
        
        error_log("🔍 [SLIDER_PARAMS] === PARAMETER BINDING ANALYSIS ===");
        error_log("🔍 [SLIDER_PARAMS] Raw parameter values BEFORE setParameters:");
        error_log("🔍 [SLIDER_PARAMS] - domainId: " . var_export($domainId, true) . " (type: " . gettype($domainId) . ")");
        error_log("🔍 [SLIDER_PARAMS] - hidden: " . var_export(false, true) . " (type: " . gettype(false) . ")");
        error_log("🔍 [SLIDER_PARAMS] - now: " . var_export($dateToday, true) . " (type: " . gettype($dateToday) . ")");
        
        $queryBuilder->setParameters($paramValues);

        // === PARAMETER BINDING VERIFICATION ===
        $setParams = $queryBuilder->getParameters();
        error_log("🔍 [SLIDER_PARAMS] Query builder parameters AFTER setParameters:");
        foreach ($setParams as $key => $value) {
            error_log("🔍 [SLIDER_PARAMS] - {$key}: " . var_export($value, true) . " (type: " . gettype($value) . ")");
        }

        $query = $queryBuilder->getQuery();

        // === ORM SQL GENERATION LOGGING ===
        error_log("🔍 [SLIDER_SQL] Generated SQL: " . $query->getSQL());
        
        $queryParams = $query->getParameters()->toArray();
        error_log("🔍 [SLIDER_SQL] Query parameters from getParameters(): " . json_encode($queryParams));
        error_log("🔍 [SLIDER_PARAMS] Query parameter details:");
        foreach ($queryParams as $key => $value) {
            error_log("🔍 [SLIDER_PARAMS] - Query param {$key}: " . var_export($value, true) . " (type: " . gettype($value) . ")");
        }

        // === QUERY EXECUTION TIMING ===
        $startTime = microtime(true);

        try {
            $result = $query->getResult();
            $executionTime = (microtime(true) - $startTime) * 1000;

            error_log("🔍 [SLIDER_TIMING] Query execution time: {$executionTime}ms");
            error_log("🔍 [SLIDER_RESULT] Query returned: " . count($result) . " records");

            if (empty($result)) {
                error_log("⚠️ [SLIDER_ISSUE] EMPTY RESULT - Connection initialization should have prevented this!");
                error_log("🔍 [SLIDER_TEST] Testing direct parameter binding to bypass ORM layer...");
                
                // === DIRECT PARAMETER BINDING TEST ===
                error_log("🔍 [SLIDER_DIRECT] === BYPASSING ORM PARAMETER BINDING ===");
                
                try {
                    // Test 1: Direct SQL with string substitution (not recommended but good for testing)
                    $directSql = "SELECT COUNT(*) FROM slider_items WHERE domain_id = {$domainId} AND hidden = false AND (datetime_visible_from IS NULL OR datetime_visible_from <= '{$dateToday}') AND (datetime_visible_to IS NULL OR datetime_visible_to >= '{$dateToday}')";
                    $directResult = $connection->executeQuery($directSql);
                    $directCount = $directResult->fetchOne();
                    error_log("🔍 [SLIDER_DIRECT] Direct SQL string substitution count: " . $directCount);
                    
                    // Test 2: DBAL Connection with proper parameter binding  
                    $dbalSql = "SELECT COUNT(*) FROM slider_items WHERE domain_id = ? AND hidden = ? AND (datetime_visible_from IS NULL OR datetime_visible_from <= ?) AND (datetime_visible_to IS NULL OR datetime_visible_to >= ?)";
                    $dbalResult = $connection->executeQuery($dbalSql, [$domainId, false, $dateToday, $dateToday]);
                    $dbalCount = $dbalResult->fetchOne();
                    error_log("🔍 [SLIDER_DIRECT] DBAL connection parameter binding count: " . $dbalCount);
                    
                    // Test 3: DBAL Connection with named parameters
                    $namedSql = "SELECT COUNT(*) FROM slider_items WHERE domain_id = :domainId AND hidden = :hidden AND (datetime_visible_from IS NULL OR datetime_visible_from <= :now) AND (datetime_visible_to IS NULL OR datetime_visible_to >= :now)";
                    $namedResult = $connection->executeQuery($namedSql, [
                        'domainId' => $domainId,
                        'hidden' => false,
                        'now' => $dateToday,
                    ]);
                    $namedCount = $namedResult->fetchOne();
                    error_log("🔍 [SLIDER_DIRECT] DBAL named parameter binding count: " . $namedCount);
                    
                    error_log("🔍 [SLIDER_DIRECT] Results comparison - Direct: {$directCount}, DBAL positional: {$dbalCount}, DBAL named: {$namedCount}");
                    
                    // === PDO LEVEL TEST ===
                    error_log("🔍 [SLIDER_PDO] === TESTING PDO LEVEL DIRECTLY ===");
                    
                    try {
                        // Get the underlying PDO connection from DBAL
                        $pdo = $connection->getNativeConnection();
                        
                        if ($pdo instanceof \PDO) {
                            error_log("🔍 [SLIDER_PDO] PDO connection obtained successfully");
                            
                            // Test PDO with prepared statements
                            $pdoSql = "SELECT COUNT(*) FROM slider_items WHERE domain_id = ? AND hidden = ? AND (datetime_visible_from IS NULL OR datetime_visible_from <= ?) AND (datetime_visible_to IS NULL OR datetime_visible_to >= ?)";
                            $pdoStmt = $pdo->prepare($pdoSql);
                            $pdoStmt->execute([$domainId, false, $dateToday, $dateToday]);
                            $pdoCount = $pdoStmt->fetchColumn();
                            error_log("🔍 [SLIDER_PDO] PDO prepared statement count: " . $pdoCount);
                            
                            // Test PDO with named parameters
                            $pdoNamedSql = "SELECT COUNT(*) FROM slider_items WHERE domain_id = :domainId AND hidden = :hidden AND (datetime_visible_from IS NULL OR datetime_visible_from <= :now) AND (datetime_visible_to IS NULL OR datetime_visible_to >= :now)";
                            $pdoNamedStmt = $pdo->prepare($pdoNamedSql);
                            $pdoNamedStmt->execute([
                                ':domainId' => $domainId,
                                ':hidden' => false,
                                ':now' => $dateToday,
                            ]);
                            $pdoNamedCount = $pdoNamedStmt->fetchColumn();
                            error_log("🔍 [SLIDER_PDO] PDO named parameters count: " . $pdoNamedCount);
                            
                            error_log("🔍 [SLIDER_PDO] PDO Results - Positional: {$pdoCount}, Named: {$pdoNamedCount}");
                        } else {
                            error_log("🔍 [SLIDER_PDO] Could not get PDO connection - type: " . gettype($pdo));
                        }
                    } catch (\Exception $e) {
                        error_log("🔍 [SLIDER_PDO] PDO test failed: " . $e->getMessage());
                    }
                    
                } catch (\Exception $e) {
                    error_log("🔍 [SLIDER_DIRECT] Direct binding test failed: " . $e->getMessage());
                }
            } else {
                error_log("✅ [SLIDER_SUCCESS] Connection initialization solution CONFIRMED - query returned results!");
            }

            return $result;

        } catch (\Exception $e) {
            $executionTime = (microtime(true) - $startTime) * 1000;
            error_log("🔍 [SLIDER_TIMING] Query execution time (failed): {$executionTime}ms");
            error_log("🚨 [SLIDER_ERROR] Query failed: " . $e->getMessage());
            error_log("🚨 [SLIDER_ERROR] Stack trace: " . $e->getTraceAsString());

            return [];
        }
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getSliderItemQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('si')
            ->from(SliderItem::class, 'si');
    }
}
