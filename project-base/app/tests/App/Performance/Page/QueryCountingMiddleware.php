<?php

declare(strict_types=1);

namespace Tests\App\Performance\Page;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware;
use Override;

final class QueryCountingMiddleware implements Middleware
{
    private int $queryCount = 0;

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function wrap(DriverInterface $driver): DriverInterface
    {
        return new QueryCountingDriver($driver, $this);
    }

    public function incrementQueryCount(): void
    {
        $this->queryCount++;
    }

    public function getQueryCount(): int
    {
        return $this->queryCount;
    }
}
