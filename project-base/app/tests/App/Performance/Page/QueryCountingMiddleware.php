<?php

declare(strict_types=1);

namespace Tests\App\Performance\Page;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware;
use Override;
use ReflectionClass;

final class QueryCountingMiddleware implements Middleware
{
    /**
     * @var string[]
     */
    private array $executedQueries = [];

    public static function createInjectedInto(Connection $connection): self
    {
        $queryCountingMiddleware = new self();

        $driverProperty = new ReflectionClass($connection)->getProperty('driver');
        $driverProperty->setValue($connection, $queryCountingMiddleware->wrap($driverProperty->getValue($connection)));
        $connection->close();

        return $queryCountingMiddleware;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function wrap(DriverInterface $driver): DriverInterface
    {
        return new QueryCountingDriver($driver, $this);
    }

    public function recordQuery(string $sql): void
    {
        $this->executedQueries[] = $sql;
    }

    public function getQueryCount(): int
    {
        return count($this->executedQueries);
    }

    /**
     * @return string[]
     */
    public function getExecutedQueries(): array
    {
        return $this->executedQueries;
    }
}
