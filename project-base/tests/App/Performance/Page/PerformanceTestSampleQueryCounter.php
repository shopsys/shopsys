<?php

declare(strict_types=1);

namespace Tests\App\Performance\Page;

use Doctrine\DBAL\Logging\SQLLogger;

class PerformanceTestSampleQueryCounter implements SQLLogger
{
    /**
     * @var int
     */
    private int $queryCount = 0;

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null): void
    {
        $this->queryCount++;
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery(): void
    {
    }

    /**
     * @return int
     */
    public function getQueryCount(): int
    {
        return $this->queryCount;
    }
}
