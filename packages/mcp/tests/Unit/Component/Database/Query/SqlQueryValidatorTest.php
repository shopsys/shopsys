<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\Database\Query;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Shopsys\McpBundle\Component\Database\Query\Exception\SqlQueryParsingException;
use Shopsys\McpBundle\Component\Database\Query\PostgresQueryParser;
use Shopsys\McpBundle\Component\Database\Query\SqlQueryValidator;
use Shopsys\McpBundle\Component\Database\Schema\ExposedSchemaProvider;

class SqlQueryValidatorTest extends TestCase
{
    public const int MAX_RETURNED_ROWS = 500;

    public function testValidateReturnsInvalidResultForEmptyQuery(): void
    {
        $sqlQueryValidator = new SqlQueryValidator(
            $this->createExposedSchemaProvider(),
            $this->createStub(PostgresQueryParser::class),
            self::MAX_RETURNED_ROWS,
        );

        $sqlQueryValidationResult = $sqlQueryValidator->validate('   ');

        $this->assertFalse($sqlQueryValidationResult->isValid);
        $this->assertNull($sqlQueryValidationResult->singleStatementSql);
        $this->assertSame(SqlQueryValidator::ERROR_EMPTY_QUERY, $sqlQueryValidationResult->errorMessage);
    }

    public function testValidateReturnsInvalidResultWhenParserThrowsException(): void
    {
        $postgresQueryParser = $this->createStub(PostgresQueryParser::class);
        $postgresQueryParser->method('parseSingleStatement')
            ->willThrowException(new SqlQueryParsingException('The SQL query could not be parsed.'));

        $sqlQueryValidator = new SqlQueryValidator(
            $this->createExposedSchemaProvider(),
            $postgresQueryParser,
            self::MAX_RETURNED_ROWS,
        );

        $sqlQueryValidationResult = $sqlQueryValidator->validate('SELECT FROM');

        $this->assertFalse($sqlQueryValidationResult->isValid);
        $this->assertNull($sqlQueryValidationResult->singleStatementSql);
        $this->assertSame('The SQL query could not be parsed.', $sqlQueryValidationResult->errorMessage);
    }

    #[DataProviderExternal(SqlQueryValidatorBasicQueriesDataProvider::class, 'provideValidQueries')]
    #[DataProviderExternal(SqlQueryValidatorSetOperationQueriesDataProvider::class, 'provideValidQueries')]
    #[DataProviderExternal(SqlQueryValidatorIdentifierCaseQueriesDataProvider::class, 'provideValidQueries')]
    #[DataProviderExternal(SqlQueryValidatorOutputColumnAliasQueriesDataProvider::class, 'provideValidQueries')]
    public function testValidateReturnsValidResultForSupportedReadOnlyQueries(
        string $sql,
    ): void {
        $sqlQueryValidationResult = $this->createSqlQueryValidator()->validate($sql);

        $this->assertTrue($sqlQueryValidationResult->isValid);
        $this->assertNull($sqlQueryValidationResult->errorMessage);
    }

    #[DataProviderExternal(SqlQueryValidatorBasicQueriesDataProvider::class, 'provideInvalidQueries')]
    #[DataProviderExternal(SqlQueryValidatorSetOperationQueriesDataProvider::class, 'provideInvalidQueries')]
    #[DataProviderExternal(SqlQueryValidatorIdentifierCaseQueriesDataProvider::class, 'provideInvalidQueries')]
    #[DataProviderExternal(SqlQueryValidatorOutputColumnAliasQueriesDataProvider::class, 'provideInvalidQueries')]
    public function testValidateReturnsInvalidResultForUnsupportedQueries(
        string $sql,
        string $expectedErrorMessage,
    ): void {
        $sqlQueryValidationResult = $this->createSqlQueryValidator()->validate($sql);

        $this->assertFalse($sqlQueryValidationResult->isValid);
        $this->assertNull($sqlQueryValidationResult->singleStatementSql);
        $this->assertSame($expectedErrorMessage, $sqlQueryValidationResult->errorMessage);
    }

    private function createSqlQueryValidator(): SqlQueryValidator
    {
        $this->skipTestIfPgQueryExtensionIsMissing();

        return new SqlQueryValidator(
            $this->createExposedSchemaProvider(),
            new PostgresQueryParser(),
            self::MAX_RETURNED_ROWS,
        );
    }

    private function createExposedSchemaProvider(): ExposedSchemaProvider
    {
        $exposedSchemaProvider = $this->createStub(ExposedSchemaProvider::class);
        $exposedSchemaProvider->method('getAllowedColumnsSetIndexedByTableNames')
            ->willReturn($this->getAllowedColumnsSetIndexedByTableNames());

        return $exposedSchemaProvider;
    }

    /**
     * @return array<string, array<string, bool>>
     */
    private function getAllowedColumnsSetIndexedByTableNames(): array
    {
        return [
            'administrators' => [
                'id' => true,
                'username' => true,
            ],
            'currencies' => [
                'code' => true,
                'exchange_rate' => true,
            ],
            'orders' => [
                'customer_id' => true,
                'id' => true,
                'currency_code' => true,
            ],
            'products' => [
                'id' => true,
                'catnum' => true,
            ],
            'product_translations' => [
                'id' => true,
                'locale' => true,
                'translatable_id' => true,
                'name' => true,
            ],
        ];
    }

    private function skipTestIfPgQueryExtensionIsMissing(): void
    {
        if (!function_exists('pg_query_split') || !function_exists('pg_query_parse')) {
            $this->markTestSkipped('The pg_query PHP extension must be installed to run validator tests.');
        }
    }
}
