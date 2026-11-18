<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\Database\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\McpBundle\Component\Database\Schema\SchemaNameNormalizer;

class SchemaNameNormalizerTest extends TestCase
{
    #[DataProvider('tableNameProvider')]
    public function testNormalizeTableNameFromString(string $tableName, string $expectedNormalizedTableName): void
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn(new PostgreSQLPlatform());
        $schemaNameNormalizer = new SchemaNameNormalizer($connection);

        $this->assertSame($expectedNormalizedTableName, $schemaNameNormalizer->normalizeTableNameFromString($tableName));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function tableNameProvider(): iterable
    {
        yield 'unquoted name' => ['gift_plans', 'gift_plans'];

        yield 'unquoted name is folded to lower case' => ['GiftPlans', 'giftplans'];

        yield 'schema qualified name' => ['public.gift_plans', 'gift_plans'];

        yield 'quoted name keeps its case' => ['"GiftPlans"', 'GiftPlans'];

        yield 'quoted schema qualified name' => ['"public"."GiftPlans"', 'GiftPlans'];
    }
}
