<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\Middleware;

use Doctrine\DBAL\Connection;
use Symfony\Component\Yaml\Yaml;
use Tests\App\Test\FunctionalTestCase;

final class McpSessionSettingsMiddlewareTest extends FunctionalTestCase
{
    /**
     * @inject doctrine.dbal.mcp_connection
     */
    private Connection $mcpConnection;

    public function testMcpConnectionUsesConfiguredSessionSettings(): void
    {
        $expectedQuerySettings = $this->getExpectedQuerySettings();

        $actualStatementTimeoutMilliseconds = (int)$this->mcpConnection->fetchOne(
            "SELECT (EXTRACT(EPOCH FROM current_setting('statement_timeout')::interval) * 1000)::int",
        );
        $actualLockTimeoutMilliseconds = (int)$this->mcpConnection->fetchOne(
            "SELECT (EXTRACT(EPOCH FROM current_setting('lock_timeout')::interval) * 1000)::int",
        );
        $defaultTransactionReadOnly = $this->mcpConnection->fetchOne(
            "SELECT current_setting('default_transaction_read_only')",
        );

        $this->assertSame($expectedQuerySettings['statement_timeout_ms'], $actualStatementTimeoutMilliseconds);
        $this->assertSame($expectedQuerySettings['lock_timeout_ms'], $actualLockTimeoutMilliseconds);
        $this->assertSame('on', $defaultTransactionReadOnly);
    }

    /**
     * @return array{statement_timeout_ms: int, lock_timeout_ms: int}
     */
    private function getExpectedQuerySettings(): array
    {
        $configuration = Yaml::parseFile(
            static::getContainer()->getParameter('kernel.project_dir') . '/config/packages/shopsys_mcp.yaml',
        );

        return $configuration['shopsys_mcp']['query'];
    }
}
