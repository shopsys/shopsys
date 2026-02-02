<?php

declare(strict_types=1);

namespace Tests\App\Performance\Page;

use Doctrine\DBAL\Logging\SQLLogger;
use Override;

class PerformanceTestSampleQueryCounter implements SQLLogger
{
    private int $queryCount = 0;

    #[Override]
    public function startQuery($sql, ?array $params = null, ?array $types = null): void
    {
        $this->queryCount++;
    }

    #[Override]
    public function stopQuery(): void
    {
    }

    public function getQueryCount(): int
    {
        return $this->queryCount;
    }
}
