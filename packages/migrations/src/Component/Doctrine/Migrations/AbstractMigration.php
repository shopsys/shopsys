<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Component\Doctrine\Migrations;

use Doctrine\DBAL\Cache\QueryCacheProfile;
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
     * {@inheritdoc}
     */
    protected function addSql(string $sql, array $params = [], array $types = []): void
    {
        $message = 'Calling method "addSql" is not allowed. Use "sql" method instead';

        throw new MethodIsNotAllowedException($message);
    }

    /**
     * @param string $query
     * @param array $params
     * @param array $types
     * @param \Doctrine\DBAL\Cache\QueryCacheProfile|null $qcp
     * @return \Doctrine\DBAL\Result
     */
    public function sql($query, array $params = [], $types = [], ?QueryCacheProfile $qcp = null)
    {
        $this->sqlQueries[] = new Query($query, $params, $types);

        return $this->connection->executeQuery($query, $params, $types, $qcp);
    }

    /**
     * {@inheritdoc}
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

    /**
     * @param string $version
     * @return bool
     */
    protected function isAppMigrationNotInstalled(string $version): bool
    {
        return !$this->sql(
            'SELECT COUNT(*) FROM migrations WHERE version = :version;',
            ['version' => $this->prefixAppMigrationVersion($version)],
        )->fetchOne();
    }

    /**
     * @param string $version
     * @return bool
     */
    protected function isAppMigrationNotInstalledRemoveIfExists(string $version): bool
    {
        $isAppMigrationNotInstalled = $this->isAppMigrationNotInstalled($version);

        if (!$isAppMigrationNotInstalled) {
            $this->sql(
                'DELETE FROM migrations WHERE version = :version;',
                ['version' => $this->prefixAppMigrationVersion($version)],
            );
        }

        return $isAppMigrationNotInstalled;
    }

    /**
     * @param string $version
     * @return string
     */
    protected function prefixAppMigrationVersion(string $version): string
    {
        return 'App\\Migrations\\' . $version;
    }
}
