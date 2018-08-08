<?php

namespace Shopsys\MigrationBundle\Component\Doctrine\Migrations;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Migrations\AbstractMigration as DoctrineAbstractMigration;

abstract class AbstractMigration extends DoctrineAbstractMigration
{

    /**
     * {@inheritDoc}
     * @deprecated use "sql" method instead
     */
    protected function addSql($sql, array $params = [], array $types = [])
    {
        $message = 'Calling method "addSql" is not allowed. Use "sql" method instead';
        throw new \Shopsys\MigrationBundle\Component\Doctrine\Migrations\Exception\MethodIsNotAllowedException($message);
    }

    /**
     * @param \Doctrine\DBAL\Cache\QueryCacheProfile|null $qcp
     */
    public function sql(string $query, array $params = [], array $types = [], QueryCacheProfile $qcp = null): \Doctrine\DBAL\Driver\Statement
    {
        return $this->connection->executeQuery($query, $params, $types, $qcp);
    }

    /**
     * {@inheritDoc}
     *
     * @see \Shopsys\MigrationBundle\Command\MigrateCommand::execute()
     */
    public function isTransactional()
    {
        // We do not want every migration to be executed in a separate transaction
        // because MigrateCommand wraps all migrations in a single transaction.
        return false;
    }
}
