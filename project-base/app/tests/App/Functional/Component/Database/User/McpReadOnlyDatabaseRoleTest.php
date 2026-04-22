<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\User;

use Doctrine\DBAL\Connection;
use Tests\App\Test\FunctionalTestCase;

final class McpReadOnlyDatabaseRoleTest extends FunctionalTestCase
{
    private const string TEST_TABLE_PREFIX = 'mcp_read_only_role_test_';
    private const string VISIBLE_ROW_VALUE = 'mcp-visible-row';

    /**
     * @inject doctrine.dbal.mcp_connection
     */
    private Connection $mcpConnection;

    /**
     * @inject doctrine.dbal.default_connection
     */
    private Connection $defaultConnection;

    public function testMcpConnectionUsesDedicatedReadOnlyDatabaseRole(): void
    {
        $mcpUser = $this->mcpConnection->fetchOne('SELECT current_user');
        $defaultConnectionCurrentUser = $this->defaultConnection->fetchOne('SELECT current_user');
        $isSuperuser = $this->mcpConnection->fetchOne(
            'SELECT rolsuper FROM pg_roles WHERE rolname = current_user',
        );
        $hasSelectPrivilege = $this->mcpConnection->fetchOne(
            "SELECT has_table_privilege(current_user, 'public.administrator_roles', 'SELECT')",
        );
        $hasInsertPrivilege = $this->mcpConnection->fetchOne(
            "SELECT has_table_privilege(current_user, 'public.administrator_roles', 'INSERT')",
        );
        $hasUpdatePrivilege = $this->mcpConnection->fetchOne(
            "SELECT has_table_privilege(current_user, 'public.administrator_roles', 'UPDATE')",
        );
        $hasDeletePrivilege = $this->mcpConnection->fetchOne(
            "SELECT has_table_privilege(current_user, 'public.administrator_roles', 'DELETE')",
        );
        $hasCreatePrivilegeOnPublicSchema = $this->mcpConnection->fetchOne(
            "SELECT has_schema_privilege(current_user, 'public', 'CREATE')",
        );

        $this->assertNotSame($defaultConnectionCurrentUser, $mcpUser);
        $this->assertFalse((bool)$isSuperuser);
        $this->assertTrue((bool)$hasSelectPrivilege);
        $this->assertFalse((bool)$hasInsertPrivilege);
        $this->assertFalse((bool)$hasUpdatePrivilege);
        $this->assertFalse((bool)$hasDeletePrivilege);
        $this->assertFalse((bool)$hasCreatePrivilegeOnPublicSchema);
    }

    public function testMcpConnectionCanReadNewTableCreatedByApplicationDatabaseUser(): void
    {
        $tableName = self::TEST_TABLE_PREFIX . bin2hex(random_bytes(6));
        $quotedTableName = $this->defaultConnection->quoteSingleIdentifier($tableName);

        try {
            $this->defaultConnection->executeStatement(sprintf(
                'CREATE TABLE public.%s (id SERIAL PRIMARY KEY, value VARCHAR(255) NOT NULL)',
                $quotedTableName,
            ));
            $this->defaultConnection->executeStatement(sprintf(
                'INSERT INTO public.%s (value) VALUES (?)',
                $quotedTableName,
            ), [self::VISIBLE_ROW_VALUE]);

            $visibleValue = $this->mcpConnection->fetchOne(sprintf(
                'SELECT value FROM public.%s LIMIT 1',
                $quotedTableName,
            ));

            $this->assertSame(self::VISIBLE_ROW_VALUE, $visibleValue);
        } finally {
            $this->defaultConnection->executeStatement(sprintf(
                'DROP TABLE IF EXISTS public.%s',
                $quotedTableName,
            ));
        }
    }
}
