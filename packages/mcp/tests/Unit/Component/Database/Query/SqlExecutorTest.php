<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\Database\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopsys\McpBundle\Component\Database\Query\QueryResultMcpNormalizer;
use Shopsys\McpBundle\Component\Database\Query\SqlExecutionResult;
use Shopsys\McpBundle\Component\Database\Query\SqlExecutor;
use Shopsys\McpBundle\Component\Database\Query\SqlQueryValidationResult;
use Shopsys\McpBundle\Component\Database\Query\SqlQueryValidator;

class SqlExecutorTest extends TestCase
{
    private const int MAX_RETURNED_ROWS = 2;
    private const string SIMPLE_SELECT_SQL = 'SELECT id FROM products LIMIT 5';

    public function testExecuteReturnsInvalidResultWhenValidationFails(): void
    {
        $sqlExecutor = $this->createSqlExecutor(
            $this->createStub(Connection::class),
            SqlQueryValidationResult::createInvalid('SQL query is invalid.'),
        );

        $sqlExecutionResult = $sqlExecutor->execute(self::SIMPLE_SELECT_SQL);

        $this->assertEquals(
            SqlExecutionResult::createInvalid('SQL query is invalid.'),
            $sqlExecutionResult,
        );
    }

    public function testExecuteReturnsInvalidResultWhenDatabaseExecutionFails(): void
    {
        $mcpConnection = $this->createStub(Connection::class);
        $mcpConnection->method('executeQuery')
            ->willThrowException(new class('Database execution failed.') extends RuntimeException implements DbalException {
            });

        $sqlExecutor = $this->createSqlExecutor(
            $mcpConnection,
            SqlQueryValidationResult::createValid(self::SIMPLE_SELECT_SQL),
        );

        $sqlExecutionResult = $sqlExecutor->execute(self::SIMPLE_SELECT_SQL);

        $this->assertEquals(
            SqlExecutionResult::createInvalid('SQL execution failed.'),
            $sqlExecutionResult,
        );
    }

    public function testExecuteReturnsAtMostConfiguredNumberOfRows(): void
    {
        $queryResult = $this->createStub(Result::class);
        $queryResult->method('fetchAssociative')
            ->willReturnOnConsecutiveCalls(
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
                false,
            );

        $mcpConnection = $this->createStub(Connection::class);
        $mcpConnection->method('executeQuery')
            ->willReturn($queryResult);

        $sqlExecutionResult = $this->createSqlExecutor(
            $mcpConnection,
            SqlQueryValidationResult::createValid(self::SIMPLE_SELECT_SQL),
        )->execute(self::SIMPLE_SELECT_SQL);

        $this->assertTrue($sqlExecutionResult->isValid);
        $this->assertSame(2, $sqlExecutionResult->data['rowCount'] ?? null);
        $this->assertSame([
            ['id' => 1],
            ['id' => 2],
        ], $sqlExecutionResult->data['rows'] ?? null);
    }

    private function createSqlExecutor(
        Connection $mcpConnection,
        SqlQueryValidationResult $sqlQueryValidationResult,
    ): SqlExecutor {
        $sqlQueryValidator = $this->createStub(SqlQueryValidator::class);
        $sqlQueryValidator->method('validate')->willReturn($sqlQueryValidationResult);

        return new SqlExecutor(
            $mcpConnection,
            new QueryResultMcpNormalizer(),
            $sqlQueryValidator,
            self::MAX_RETURNED_ROWS,
        );
    }
}
