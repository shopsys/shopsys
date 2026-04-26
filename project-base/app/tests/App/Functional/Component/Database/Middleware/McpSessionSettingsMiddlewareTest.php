<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\Middleware;

use Doctrine\DBAL\Connection;
use Tests\App\Test\FunctionalTestCase;

final class McpSessionSettingsMiddlewareTest extends FunctionalTestCase
{
    /**
     * @inject doctrine.dbal.mcp_connection
     */
    private Connection $mcpConnection;

    public function testMcpConnectionUsesConfiguredSessionSettings(): void
    {
        $expectedStatementTimeoutMilliseconds = (int)static::getContainer()
            ->getParameter('shopsys_mcp.query.statement_timeout_ms');
        $expectedLockTimeoutMilliseconds = (int)static::getContainer()
            ->getParameter('shopsys_mcp.query.lock_timeout_ms');

        $actualStatementTimeoutMilliseconds = (int)$this->mcpConnection->fetchOne(
            "SELECT (EXTRACT(EPOCH FROM current_setting('statement_timeout')::interval) * 1000)::int",
        );
        $actualLockTimeoutMilliseconds = (int)$this->mcpConnection->fetchOne(
            "SELECT (EXTRACT(EPOCH FROM current_setting('lock_timeout')::interval) * 1000)::int",
        );
        $defaultTransactionReadOnly = $this->mcpConnection->fetchOne(
            "SELECT current_setting('default_transaction_read_only')",
        );

        $this->assertSame($expectedStatementTimeoutMilliseconds, $actualStatementTimeoutMilliseconds);
        $this->assertSame($expectedLockTimeoutMilliseconds, $actualLockTimeoutMilliseconds);
        $this->assertSame('on', $defaultTransactionReadOnly);
    }
}
