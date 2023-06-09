<?php

declare(strict_types=1);

namespace Tests\App\Performance\Page;

use Doctrine\DBAL\Logging\SQLLogger;

class PerformanceTestSampleQueryCounter implements SQLLogger
{
    private int $queryCount = 0;

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        $this->queryCount++;
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
    }

    /**
     * @return int
     */
    public function getQueryCount()
    {
        return $this->queryCount;
    }
}
