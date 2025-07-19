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
        // === CONNECTION STATUS LOGGING ===
        $connection = $this->em->getConnection();
        error_log("🔍 [SLIDER_CONN] Connection established: " . ($connection->isConnected() ? 'YES' : 'NO'));

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

        $queryBuilder->setParameters([
            'domainId' => $domainId,
            'hidden' => false,
            'now' => $dateToday,
        ]);

        $query = $queryBuilder->getQuery();

        // === ORM SQL GENERATION LOGGING ===
        error_log("🔍 [SLIDER_SQL] Generated SQL: " . $query->getSQL());
        error_log("🔍 [SLIDER_SQL] Parameters: " . json_encode($query->getParameters()->toArray()));

        // === QUERY EXECUTION TIMING ===
        $startTime = microtime(true);

        try {
            $result = $query->getResult();
            $executionTime = (microtime(true) - $startTime) * 1000;

            error_log("🔍 [SLIDER_TIMING] Query execution time: {$executionTime}ms");
            error_log("🔍 [SLIDER_RESULT] Query returned: " . count($result) . " records");

            if (empty($result)) {
                error_log("⚠️ [SLIDER_ISSUE] EMPTY RESULT - This is the issue!");

                // === PHASE 1 VERIFICATION: MANUAL CONNECTION RESET TEST ===
                // Test theory: Manual connection reset should replicate the "fix"
                
                // Check PostgreSQL search_path BEFORE reset
                try {
                    $initialPath = $connection->executeQuery('SHOW search_path;')->fetchOne();
                    error_log("🔍 [SLIDER_PHASE1] Initial search_path: " . $initialPath);
                } catch (\Exception $e) {
                    error_log("🔍 [SLIDER_PHASE1] Could not check initial search_path: " . $e->getMessage());
                }
                
                // Manual connection reset (replaces getHost() exception)
                $connection->getDatabase(); // Ensure connection exists
                $connection->close();       // Manual reset - this should trigger the "fix"
                error_log("🔍 [SLIDER_PHASE1] Connection manually reset - testing if this replicates the fix");
                
                // Check PostgreSQL search_path AFTER reset
                try {
                    $newPath = $connection->executeQuery('SHOW search_path;')->fetchOne();
                    error_log("🔍 [SLIDER_PHASE1] New search_path after reset: " . $newPath);
                } catch (\Exception $e) {
                    error_log("🔍 [SLIDER_PHASE1] Could not check new search_path: " . $e->getMessage());
                }

                // === TRANSACTION STATE ANALYSIS ===
                error_log("🔍 [SLIDER_DIAG] === TRANSACTION STATE ===");
                try {
                    $transactionLevel = $connection->getTransactionNestingLevel();
                    error_log("🔍 [SLIDER_DIAG] Transaction nesting level: " . $transactionLevel);

                    $inTransaction = $connection->isTransactionActive();
                    error_log("🔍 [SLIDER_DIAG] In active transaction: " . ($inTransaction ? 'YES' : 'NO'));
                } catch (\Exception $e) {
                    error_log("🔍 [SLIDER_DIAG] Transaction state check failed: " . $e->getMessage());
                }

                // === SCHEMA AND SEARCH PATH ANALYSIS ===
                // COMMENTED OUT - Testing if schema queries cause connection warming
                // error_log("🔍 [SLIDER_DIAG] === SCHEMA ANALYSIS ===");
                // try {
                //     $schemaResult = $connection->executeQuery("SELECT current_schema()");
                //     $currentSchema = $schemaResult->fetchOne();
                //     error_log("🔍 [SLIDER_DIAG] Current schema: " . $currentSchema);
                //
                //     $searchPathResult = $connection->executeQuery("SHOW search_path");
                //     $searchPath = $searchPathResult->fetchOne();
                //     error_log("🔍 [SLIDER_DIAG] Search path: " . $searchPath);
                // } catch (\Exception $e) {
                //     error_log("🔍 [SLIDER_DIAG] Schema check failed: " . $e->getMessage());
                // }

                // === ENTITY MANAGER STATE ANALYSIS ===
                error_log("🔍 [SLIDER_DIAG] === ENTITY MANAGER STATE ===");
                try {
                    $uow = $this->em->getUnitOfWork();
                    $identityMapSize = count($uow->getIdentityMap());
                    error_log("🔍 [SLIDER_DIAG] Identity map size: " . $identityMapSize);

                    $isOpen = $this->em->isOpen();
                    error_log("🔍 [SLIDER_DIAG] Entity manager open: " . ($isOpen ? 'YES' : 'NO'));
                } catch (\Exception $e) {
                    error_log("🔍 [SLIDER_DIAG] Entity manager state check failed: " . $e->getMessage());
                }

                // === ENHANCED RAW SQL COMPARISON WITH EXACT PARAMETERS ===
                error_log("🔍 [SLIDER_DIAG] === RAW SQL COMPARISON ===");

                // Use EXACT same parameters as ORM (boolean false, not string)
                $rawSql = "SELECT COUNT(*) FROM slider_items si 
                          WHERE si.domain_id = :domainId 
                          AND si.hidden = :hidden 
                          AND (si.datetime_visible_from IS NULL OR si.datetime_visible_from <= :now) 
                          AND (si.datetime_visible_to IS NULL OR si.datetime_visible_to >= :now)";

                $rawResult = $connection->executeQuery($rawSql, [
                    'domainId' => $domainId,
                    'hidden' => false, // Use boolean like ORM, not string
                    'now' => $dateToday,
                ]);
                $rawCount = $rawResult->fetchOne();

                error_log("🔍 [SLIDER_DIAG] Raw SQL count (exact ORM params): " . $rawCount);

                // === ADDITIONAL RAW SQL WITH DIFFERENT PARAMETER TYPES ===
                $rawSqlString = "SELECT COUNT(*) FROM slider_items si 
                               WHERE si.domain_id = :domainId 
                               AND si.hidden = :hidden 
                               AND (si.datetime_visible_from IS NULL OR si.datetime_visible_from <= :now) 
                               AND (si.datetime_visible_to IS NULL OR si.datetime_visible_to >= :now)";

                $rawResultString = $connection->executeQuery($rawSqlString, [
                    'domainId' => $domainId,
                    'hidden' => 'false', // Use string
                    'now' => $dateToday,
                ]);
                $rawCountString = $rawResultString->fetchOne();

                error_log("🔍 [SLIDER_DIAG] Raw SQL count (string params): " . $rawCountString);

                // === TABLE EXISTENCE AND PERMISSION CHECK ===
                // COMMENTED OUT - Testing if table verification queries cause connection warming
                // error_log("🔍 [SLIDER_DIAG] === TABLE VERIFICATION ===");
                // try {
                //     $tableCheckSql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'slider_items'";
                //     $tableResult = $connection->executeQuery($tableCheckSql);
                //     $tableExists = $tableResult->fetchOne();
                //     error_log("🔍 [SLIDER_DIAG] slider_items table exists: " . ($tableExists > 0 ? 'YES' : 'NO'));
                //
                //     if ($tableExists > 0) {
                //         $totalRowsSql = "SELECT COUNT(*) FROM slider_items";
                //         $totalResult = $connection->executeQuery($totalRowsSql);
                //         $totalRows = $totalResult->fetchOne();
                //         error_log("🔍 [SLIDER_DIAG] Total rows in slider_items: " . $totalRows);
                //     }
                // } catch (\Exception $e) {
                //     error_log("🔍 [SLIDER_DIAG] Table verification failed: " . $e->getMessage());
                // }

                // === CRITICAL ANALYSIS ===
                if ($rawCount > 0 || $rawCountString > 0) {
                    error_log("🚨 [SLIDER_CRITICAL] Raw SQL has data but ORM returns empty!");
                    error_log("🚨 [SLIDER_CRITICAL] This confirms ORM connection/state issue!");
                    error_log("🚨 [SLIDER_CRITICAL] Boolean params: $rawCount, String params: $rawCountString");
                } else {
                    error_log("🔍 [SLIDER_DIAG] Both raw SQL and ORM return empty - data issue confirmed");
                }
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
