<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\Availability;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\McpBundle\Component\Availability\McpAvailabilityChecker;

class McpAvailabilityCheckerTest extends TestCase
{
    #[DataProvider('availabilityDataProvider')]
    public function testIsAvailableRequiresBothDatabaseCredentials(
        string $mcpDatabaseUser,
        string $mcpDatabasePassword,
        bool $expectedIsAvailable,
    ): void {
        $mcpAvailabilityChecker = new McpAvailabilityChecker(
            $mcpDatabaseUser,
            $mcpDatabasePassword,
        );

        $this->assertSame($expectedIsAvailable, $mcpAvailabilityChecker->isAvailable());
    }

    /**
     * @return iterable<string, array{mcpDatabaseUser: string, mcpDatabasePassword: string, expectedIsAvailable: bool}>
     */
    public static function availabilityDataProvider(): iterable
    {
        yield 'both credentials configured' => [
            'mcpDatabaseUser' => 'shopsys_mcp',
            'mcpDatabasePassword' => 'secret',
            'expectedIsAvailable' => true,
        ];

        yield 'database user is missing' => [
            'mcpDatabaseUser' => '',
            'mcpDatabasePassword' => 'secret',
            'expectedIsAvailable' => false,
        ];

        yield 'database password is missing' => [
            'mcpDatabaseUser' => 'shopsys_mcp',
            'mcpDatabasePassword' => '',
            'expectedIsAvailable' => false,
        ];

        yield 'both credentials are missing' => [
            'mcpDatabaseUser' => '',
            'mcpDatabasePassword' => '',
            'expectedIsAvailable' => false,
        ];

        yield 'database user contains only whitespace' => [
            'mcpDatabaseUser' => '  ',
            'mcpDatabasePassword' => 'secret',
            'expectedIsAvailable' => false,
        ];

        yield 'database password contains only whitespace' => [
            'mcpDatabaseUser' => 'shopsys_mcp',
            'mcpDatabasePassword' => '  ',
            'expectedIsAvailable' => false,
        ];
    }
}
