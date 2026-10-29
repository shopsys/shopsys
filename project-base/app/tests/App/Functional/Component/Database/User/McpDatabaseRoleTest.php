<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\User;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DriverException;
use Shopsys\McpBundle\Component\Database\User\McpDatabaseUserFacade;
use Shopsys\McpBundle\Component\Database\View\McpDatabaseViewsFacade;
use Tests\App\Test\FunctionalTestCase;

final class McpDatabaseRoleTest extends FunctionalTestCase
{
    private const string TEST_TABLE_PREFIX = 'mcp_database_role_test_';

    /**
     * @inject doctrine.dbal.mcp_connection
     */
    private Connection $mcpConnection;

    /**
     * @inject doctrine.dbal.default_connection
     */
    private Connection $defaultConnection;

    /**
     * @inject
     */
    private McpDatabaseViewsFacade $mcpDatabaseViewsFacade;

    public function testMcpConnectionUsesDedicatedDatabaseRole(): void
    {
        $viewsSchemaName = $this->mcpDatabaseViewsFacade->getViewsSchemaName();
        $viewName = $this->defaultConnection->quote($viewsSchemaName . '.administrators');
        $mcpUser = $this->mcpConnection->fetchOne('SELECT current_user');
        $defaultConnectionCurrentUser = $this->defaultConnection->fetchOne('SELECT current_user');
        $isSuperuser = $this->mcpConnection->fetchOne(
            'SELECT rolsuper FROM pg_roles WHERE rolname = current_user',
        );
        $connectionLimit = $this->mcpConnection->fetchOne(
            'SELECT rolconnlimit FROM pg_roles WHERE rolname = current_user',
        );
        $roleSettings = $this->mcpConnection->fetchFirstColumn(
            'SELECT unnest(setconfig) FROM pg_db_role_setting WHERE setrole = current_user::regrole AND setdatabase = 0',
        );
        $hasSelectPrivilegeOnView = $this->mcpConnection->fetchOne(
            sprintf("SELECT has_table_privilege(current_user, %s, 'SELECT')", $viewName),
        );
        $hasInsertPrivilegeOnView = $this->mcpConnection->fetchOne(
            sprintf("SELECT has_table_privilege(current_user, %s, 'INSERT')", $viewName),
        );
        $hasUsagePrivilegeOnPublicSchema = $this->mcpConnection->fetchOne(
            "SELECT has_schema_privilege(current_user, 'public', 'USAGE')",
        );
        $hasCreatePrivilegeOnPublicSchema = $this->mcpConnection->fetchOne(
            "SELECT has_schema_privilege(current_user, 'public', 'CREATE')",
        );

        $this->assertNotSame($defaultConnectionCurrentUser, $mcpUser);
        $this->assertFalse((bool)$isSuperuser);
        $this->assertSame(McpDatabaseUserFacade::CONNECTION_LIMIT, (int)$connectionLimit);
        $this->assertEqualsCanonicalizing([
            'search_path=' . $viewsSchemaName,
            'default_transaction_read_only=on',
            'work_mem=' . McpDatabaseUserFacade::WORK_MEM,
            'temp_file_limit=' . McpDatabaseUserFacade::TEMP_FILE_LIMIT,
        ], $roleSettings);
        $this->assertTrue((bool)$hasSelectPrivilegeOnView);
        $this->assertFalse((bool)$hasInsertPrivilegeOnView);
        $this->assertTrue((bool)$hasUsagePrivilegeOnPublicSchema);
        $this->assertFalse((bool)$hasCreatePrivilegeOnPublicSchema);
    }

    public function testMcpConnectionCannotReadTablesOfPublicSchema(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('permission denied');

        $this->mcpConnection->fetchOne('SELECT password FROM public.administrators LIMIT 1');
    }

    public function testMcpConnectionCannotReadNewTableCreatedByApplicationDatabaseUser(): void
    {
        $tableName = self::TEST_TABLE_PREFIX . bin2hex(random_bytes(6));
        $quotedTableName = $this->defaultConnection->quoteSingleIdentifier($tableName);

        try {
            $this->defaultConnection->executeStatement(sprintf(
                'CREATE TABLE public.%s (id SERIAL PRIMARY KEY, value VARCHAR(255) NOT NULL)',
                $quotedTableName,
            ));

            $this->expectException(DriverException::class);
            $this->expectExceptionMessage('permission denied');

            $this->mcpConnection->fetchOne(sprintf(
                'SELECT value FROM public.%s LIMIT 1',
                $quotedTableName,
            ));
        } finally {
            $this->defaultConnection->executeStatement(sprintf(
                'DROP TABLE IF EXISTS public.%s',
                $quotedTableName,
            ));
        }
    }
}
