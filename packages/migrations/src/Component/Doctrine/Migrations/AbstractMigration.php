<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Component\Doctrine\Migrations;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration as DoctrineAbstractMigration;
use Doctrine\Migrations\Query\Query;
use Exception;
use Override;
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
    #[Override]
    protected function addSql(string $sql, array $params = [], array $types = []): void
    {
        $message = 'Calling method "addSql" is not allowed. Use "sql" method instead';

        throw new MethodIsNotAllowedException($message);
    }

    public function sql(string $query, array $params = [], array $types = [], ?QueryCacheProfile $qcp = null): Result
    {
        $this->sqlQueries[] = new Query($query, $params, $types);

        return $this->connection->executeQuery($query, $params, $types, $qcp);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Shopsys\MigrationBundle\Command\MigrateCommand::execute()
     */
    #[Override]
    public function isTransactional(): bool
    {
        // We do not want every migration to be executed in a separate transaction
        // because MigrateCommand wraps all migrations in a single transaction.
        return false;
    }

    /**
     * @return \Doctrine\Migrations\Query\Query[]
     */
    #[Override]
    public function getSql(): array
    {
        return $this->sqlQueries;
    }

    protected function isAppMigrationNotInstalled(string $version): bool
    {
        return !$this->sql(
            'SELECT COUNT(*) FROM migrations WHERE version = :version;',
            ['version' => $this->prefixAppMigrationVersion($version)],
        )->fetchOne();
    }

    protected function isAppMigrationNotInstalledRemoveIfExists(string $version): bool
    {
        $isAppMigrationNotInstalled = $this->isAppMigrationNotInstalled($version);

        if (!$isAppMigrationNotInstalled) {
            $appMigrationVersion = $this->prefixAppMigrationVersion($version);
            $this->sql(
                'DELETE FROM migrations WHERE version = :version;',
                ['version' => $appMigrationVersion],
            );

            if (class_exists($appMigrationVersion)) {
                throw new Exception(sprintf('%s migration file must be removed as the migration is now replaced by %s', $appMigrationVersion, static::class));
            }
        }

        return $isAppMigrationNotInstalled;
    }

    protected function prefixAppMigrationVersion(string $version): string
    {
        return 'App\\Migrations\\' . $version;
    }

    protected function columnExists(string $tableName, string $columnName): bool
    {
        return $this->sql('SELECT EXISTS (SELECT FROM information_schema.columns WHERE table_name = :table_name AND column_name = :column_name)', [
            'table_name' => $tableName,
            'column_name' => $columnName,
        ])->fetchOne();
    }

    protected function tableExists(string $tableName): bool
    {
        return $this->sql('SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = :table_name)', [
            'table_name' => $tableName,
        ])->fetchOne();
    }

    #[Override]
    public function down(Schema $schema): void
    {
    }
}
