<?php

declare(strict_types=1);

namespace Tests\App\Performance\Page;

use Doctrine\DBAL\Logging\SQLLogger;
use Override;

class PerformanceTestSampleQueryCounter implements SQLLogger
{
    private int $queryCount = 0;

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        $this->queryCount++;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
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
