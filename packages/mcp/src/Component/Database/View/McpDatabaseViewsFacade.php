<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\View;

use Doctrine\DBAL\Connection;
use Shopsys\FrameworkBundle\Component\Doctrine\DatabaseSchemaFacade;
use Shopsys\McpBundle\Component\Database\Schema\AllowedDatabaseColumnsProvider;
use Shopsys\McpBundle\Component\Database\User\McpDatabaseRoleRepository;
use Shopsys\McpBundle\Component\Database\View\Exception\McpDatabaseRoleNotFoundException;
use Shopsys\McpBundle\Component\Database\View\Exception\McpDatabaseRoleWithoutPublicSchemaUsageException;

class McpDatabaseViewsFacade
{
    protected const string PUBLIC_SCHEMA_NAME = 'public';

    public function __construct(
        protected readonly Connection $defaultConnection,
        protected readonly DatabaseSchemaFacade $databaseSchemaFacade,
        protected readonly AllowedDatabaseColumnsProvider $allowedDatabaseColumnsProvider,
        protected readonly McpDatabaseRoleRepository $mcpDatabaseRoleRepository,
        protected readonly McpDatabaseViewsRepository $mcpDatabaseViewsRepository,
        protected readonly string $mcpDatabaseUser,
        protected readonly string $viewsSchemaName,
    ) {
    }

    public function getViewsSchemaName(): string
    {
        return $this->viewsSchemaName;
    }

    public function dropViews(): void
    {
        $this->databaseSchemaFacade->dropSchemaIfExists($this->viewsSchemaName);
    }

    /**
     * @return int the number of the created views
     */
    public function recreateViews(): int
    {
        if (!$this->mcpDatabaseRoleRepository->roleExists($this->mcpDatabaseUser)) {
            throw new McpDatabaseRoleNotFoundException($this->mcpDatabaseUser);
        }

        if (!$this->mcpDatabaseRoleRepository->canRoleUsePublicSchema($this->mcpDatabaseUser)) {
            throw new McpDatabaseRoleWithoutPublicSchemaUsageException($this->mcpDatabaseUser);
        }

        return $this->defaultConnection->transactional(function (): int {
            $this->dropViews();
            $this->databaseSchemaFacade->createSchema($this->viewsSchemaName);
            $exposedColumnNamesIndexedByTableNames = $this->getExposedColumnNamesIndexedByTableNames();

            foreach ($exposedColumnNamesIndexedByTableNames as $tableName => $columnNames) {
                $this->mcpDatabaseViewsRepository->createView($this->viewsSchemaName, self::PUBLIC_SCHEMA_NAME, $tableName, $columnNames);
            }

            $this->mcpDatabaseViewsRepository->grantSelectOnViews($this->viewsSchemaName, $this->mcpDatabaseUser);

            return count($exposedColumnNamesIndexedByTableNames);
        });
    }

    /**
     * @return array<string, list<string>>
     */
    protected function getExposedColumnNamesIndexedByTableNames(): array
    {
        $allowedColumnsSetIndexedByTableNames = $this->allowedDatabaseColumnsProvider->getAllAllowedColumnsSetIndexedByTableNames();
        $publicSchemaColumnNamesIndexedByTableNames = $this->mcpDatabaseViewsRepository->getColumnNamesIndexedByTableNames(self::PUBLIC_SCHEMA_NAME);
        $exposedColumnNamesIndexedByTableNames = [];

        foreach ($publicSchemaColumnNamesIndexedByTableNames as $tableName => $columnNames) {
            foreach ($columnNames as $columnName) {
                if (array_key_exists($columnName, $allowedColumnsSetIndexedByTableNames[$tableName] ?? [])) {
                    $exposedColumnNamesIndexedByTableNames[$tableName][] = $columnName;
                }
            }
        }

        return $exposedColumnNamesIndexedByTableNames;
    }
}
