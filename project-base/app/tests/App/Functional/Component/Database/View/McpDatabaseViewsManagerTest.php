<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\View;

use Shopsys\McpBundle\Component\Availability\McpAvailabilityChecker;
use Shopsys\McpBundle\Component\Database\Schema\AllowedDatabaseColumnsProvider;
use Shopsys\McpBundle\Component\Database\Schema\ExposedSchemaProvider;
use Shopsys\McpBundle\Component\Database\User\McpDatabaseRoleChecker;
use Shopsys\McpBundle\Component\Database\View\Exception\McpDatabaseRoleNotFoundException;
use Shopsys\McpBundle\Component\Database\View\Exception\McpDatabaseRoleWithoutPublicSchemaUsageException;
use Shopsys\McpBundle\Component\Database\View\McpDatabaseViewsManager;
use Shopsys\McpBundle\EventSubscriber\MigrationsSubscriber;
use Shopsys\MigrationBundle\Event\MigrationsFinishedEvent;
use Shopsys\MigrationBundle\Event\MigrationsStartingEvent;
use Tests\App\Test\TransactionFunctionalTestCase;

class McpDatabaseViewsManagerTest extends TransactionFunctionalTestCase
{
    private const string NON_EXISTENT_MCP_DATABASE_USER = 'mcp_views_test_non_existent_role';
    private const string MCP_DATABASE_USER_WITHOUT_PUBLIC_SCHEMA_USAGE = 'mcp_views_test_role_without_public_schema_usage';

    /**
     * @inject
     */
    private McpDatabaseViewsManager $mcpDatabaseViewsManager;

    /**
     * @inject
     */
    private ExposedSchemaProvider $exposedSchemaProvider;

    /**
     * @inject
     */
    private AllowedDatabaseColumnsProvider $allowedDatabaseColumnsProvider;

    public function testViewsContainExposedColumnsOfExposedTables(): void
    {
        $this->mcpDatabaseViewsManager->recreateViews();

        $expectedColumnNamesIndexedByTableNames = array_map(
            static fn (array $tableSchema): array => array_keys($tableSchema['columns']),
            $this->exposedSchemaProvider->getExposedSchema([]),
        );

        $this->assertSame(
            $this->sortColumnNamesIndexedByTableNames($expectedColumnNamesIndexedByTableNames),
            $this->sortColumnNamesIndexedByTableNames($this->getViewColumnNamesIndexedByViewNames()),
        );
    }

    public function testMigrationCanDropColumnUsedByView(): void
    {
        $this->eventDispatcher->dispatch(new MigrationsStartingEvent());
        $this->em->getConnection()->executeStatement('ALTER TABLE administrators DROP COLUMN username');
        $this->eventDispatcher->dispatch(new MigrationsFinishedEvent());

        $viewColumnNamesIndexedByViewNames = $this->getViewColumnNamesIndexedByViewNames();

        $this->assertArrayHasKey('administrators', $viewColumnNamesIndexedByViewNames);
        $this->assertNotContains('username', $viewColumnNamesIndexedByViewNames['administrators']);
    }

    public function testMigrationsOnlyDropViewsWhenMcpIsNotAvailable(): void
    {
        $mcpAvailabilityChecker = $this->createStub(McpAvailabilityChecker::class);
        $mcpAvailabilityChecker->method('isAvailable')->willReturn(false);
        $migrationsSubscriber = new MigrationsSubscriber($this->mcpDatabaseViewsManager, $mcpAvailabilityChecker);

        $migrationsSubscriber->dropMcpViews();
        $migrationsSubscriber->recreateMcpViews();

        $this->assertSame([], $this->getViewColumnNamesIndexedByViewNames());
    }

    public function testRecreateViewsFailsWhenMcpDatabaseRoleDoesNotExist(): void
    {
        $this->expectException(McpDatabaseRoleNotFoundException::class);

        $this->createMcpDatabaseViewsManager(self::NON_EXISTENT_MCP_DATABASE_USER)->recreateViews();
    }

    public function testRecreateViewsFailsWhenMcpDatabaseRoleCannotUsePublicSchema(): void
    {
        // roles are created transactionally, so the rollback of the test removes it
        $this->em->getConnection()->executeStatement(sprintf('CREATE ROLE %s', self::MCP_DATABASE_USER_WITHOUT_PUBLIC_SCHEMA_USAGE));

        $this->expectException(McpDatabaseRoleWithoutPublicSchemaUsageException::class);

        $this->createMcpDatabaseViewsManager(self::MCP_DATABASE_USER_WITHOUT_PUBLIC_SCHEMA_USAGE)->recreateViews();
    }

    private function createMcpDatabaseViewsManager(string $mcpDatabaseUser): McpDatabaseViewsManager
    {
        $connection = $this->em->getConnection();

        return new McpDatabaseViewsManager(
            $connection,
            $this->allowedDatabaseColumnsProvider,
            new McpDatabaseRoleChecker($connection, $mcpDatabaseUser),
            $mcpDatabaseUser,
        );
    }

    /**
     * @return array<string, list<string>>
     */
    private function getViewColumnNamesIndexedByViewNames(): array
    {
        $viewColumns = $this->em->getConnection()->fetchAllAssociative(
            'SELECT table_name, column_name FROM information_schema.columns WHERE table_schema = ? ORDER BY table_name, ordinal_position',
            [McpDatabaseViewsManager::VIEWS_SCHEMA_NAME],
        );
        $viewColumnNamesIndexedByViewNames = [];

        foreach ($viewColumns as $viewColumn) {
            $viewColumnNamesIndexedByViewNames[(string)$viewColumn['table_name']][] = (string)$viewColumn['column_name'];
        }

        return $viewColumnNamesIndexedByViewNames;
    }

    /**
     * @param array<string, list<string>> $columnNamesIndexedByTableNames
     * @return array<string, list<string>>
     */
    private function sortColumnNamesIndexedByTableNames(array $columnNamesIndexedByTableNames): array
    {
        ksort($columnNamesIndexedByTableNames);

        foreach ($columnNamesIndexedByTableNames as &$columnNames) {
            sort($columnNames);
        }

        return $columnNamesIndexedByTableNames;
    }
}
