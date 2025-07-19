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
                error_log("⚠️ [SLIDER_ISSUE] EMPTY RESULT - Connection initialization should have prevented this!");
                error_log("🔍 [SLIDER_TEST] If we still get empty results, deeper investigation needed");
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
