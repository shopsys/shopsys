<?php

namespace Shopsys\MigrationBundle\Component\Doctrine\Migrations;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Result;
use Doctrine\Migrations\AbstractMigration as DoctrineAbstractMigration;
use Doctrine\Migrations\Query\Query;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\Exception\MethodIsNotAllowedException;

abstract class AbstractMigration extends DoctrineAbstractMigration
{
    /**
     * @var \Doctrine\Migrations\Query\Query[]
     */
    protected array $sqlQueries = [];

    /**
     * {@inheritDoc}
     */
    protected function addSql(string $sql, array $params = [], array $types = []): void
    {
        $message = 'Calling method "addSql" is not allowed. Use "sql" method instead';
        throw new MethodIsNotAllowedException($message);
    }

    /**
     * @param string $query
     * @param list<mixed>|array<string, mixed> $params
     * @param array<int, (int|string|\Doctrine\DBAL\Types\Type|null)>|array<string, (int|string|\Doctrine\DBAL\Types\Type|null)> $types
     * @param \Doctrine\DBAL\Cache\QueryCacheProfile|null $qcp
     * @return \Doctrine\DBAL\Result
     */
    public function sql(string $query, array $params = [], array $types = [], ?QueryCacheProfile $qcp = null): Result
    {
        $this->sqlQueries[] = new Query($query, $params, $types);

        return $this->connection->executeQuery($query, $params, $types, $qcp);
    }

    /**
     * {@inheritDoc}
     *
     * @see \Shopsys\MigrationBundle\Command\MigrateCommand::execute()
     */
    public function isTransactional(): bool
    {
        // We do not want every migration to be executed in a separate transaction
        // because MigrateCommand wraps all migrations in a single transaction.
        return false;
    }

    /**
     * @return \Doctrine\Migrations\Query\Query[]
     */
    public function getSql(): array
    {
        return $this->sqlQueries;
    }
}
