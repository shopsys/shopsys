<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\PostDeploy\Task;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\McpBundle\Component\Availability\McpAvailabilityChecker;
use Shopsys\McpBundle\Component\Database\User\McpDatabaseRoleRepository;
use Shopsys\McpBundle\Component\PostDeploy\Task\CheckMcpDatabaseRolePrivilegesTask;
use Shopsys\McpBundle\Component\PostDeploy\Task\Exception\McpDatabaseRoleCanReadPublicSchemaTablesException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckMcpDatabaseRolePrivilegesTaskTest extends TestCase
{
    private const string DATABASE_USER = 'shopsys';
    private const string MCP_DATABASE_USER = 'shopsys_mcp';

    /**
     * The role reports privileges on the tables in the public schema, so the task would fail if it checked them
     */
    #[DataProvider('provideUncheckedMcpDatabaseUsers')]
    public function testRoleIsNotCheckedWithoutDedicatedMcpDatabaseRole(string $mcpDatabaseUser): void
    {
        $this->expectNotToPerformAssertions();

        $this->createTask($mcpDatabaseUser, ['administrators'], true)->run($this->createStyle());
    }

    /**
     * @return iterable<string, array{mcpDatabaseUser: string}>
     */
    public static function provideUncheckedMcpDatabaseUsers(): iterable
    {
        yield 'MCP is not configured' => [
            'mcpDatabaseUser' => '',
        ];

        yield 'MCP uses the application database user' => [
            'mcpDatabaseUser' => self::DATABASE_USER,
        ];
    }

    public function testRoleWithoutPrivilegesOnPublicSchemaTablesPasses(): void
    {
        $this->expectNotToPerformAssertions();

        $this->createTask(self::MCP_DATABASE_USER, [], false)->run($this->createStyle());
    }

    /**
     * @param list<string> $readableTableNames
     */
    #[DataProvider('providePrivilegesOnPublicSchemaTables')]
    public function testRoleWithPrivilegesOnPublicSchemaTablesFails(
        array $readableTableNames,
        bool $hasDefaultPrivileges,
    ): void {
        $this->expectException(McpDatabaseRoleCanReadPublicSchemaTablesException::class);

        $this->createTask(self::MCP_DATABASE_USER, $readableTableNames, $hasDefaultPrivileges)->run($this->createStyle());
    }

    /**
     * @return iterable<string, array{readableTableNames: list<string>, hasDefaultPrivileges: bool}>
     */
    public static function providePrivilegesOnPublicSchemaTables(): iterable
    {
        yield 'role reads the tables' => [
            'readableTableNames' => ['administrators', 'customer_users'],
            'hasDefaultPrivileges' => false,
        ];

        yield 'role has default privileges on the tables created in the future' => [
            'readableTableNames' => [],
            'hasDefaultPrivileges' => true,
        ];
    }

    /**
     * @param list<string> $readableTableNames
     */
    private function createTask(
        string $mcpDatabaseUser,
        array $readableTableNames,
        bool $hasDefaultPrivileges,
    ): CheckMcpDatabaseRolePrivilegesTask {
        $mcpDatabaseRoleRepository = $this->createStub(McpDatabaseRoleRepository::class);
        $mcpDatabaseRoleRepository->method('getPublicSchemaTableNamesReadableByRole')->willReturn($readableTableNames);
        $mcpDatabaseRoleRepository->method('hasDefaultPrivilegesOnPublicSchemaTables')->willReturn($hasDefaultPrivileges);

        return new CheckMcpDatabaseRolePrivilegesTask(
            new McpAvailabilityChecker($mcpDatabaseUser, $mcpDatabaseUser === '' ? '' : 'secret'),
            $mcpDatabaseRoleRepository,
            self::DATABASE_USER,
            $mcpDatabaseUser,
        );
    }

    private function createStyle(): SymfonyStyle
    {
        return new SymfonyStyle(new ArrayInput([]), new NullOutput());
    }
}
