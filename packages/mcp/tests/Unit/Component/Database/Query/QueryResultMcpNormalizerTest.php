<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\Database\Query;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\McpBundle\Component\Database\Query\QueryResultMcpNormalizer;
use Stringable;

class QueryResultMcpNormalizerTest extends TestCase
{
    #[DataProvider('provideNormalizedRows')]
    public function testNormalizeRowsReturnsMcpSafeScalars(
        mixed $value,
        string|int|float|bool|null $expectedValue,
    ): void {
        $queryResultMcpNormalizer = new QueryResultMcpNormalizer();

        $normalizedRows = $queryResultMcpNormalizer->normalizeRows([
            ['value' => $value],
        ]);

        $this->assertSame([['value' => $expectedValue]], $normalizedRows);
    }

    /**
     * @return iterable<string, array{value: mixed, expectedValue: string|int|float|bool|null}>
     */
    public static function provideNormalizedRows(): iterable
    {
        yield 'string stays unchanged' => [
            'value' => 'shopsys',
            'expectedValue' => 'shopsys',
        ];

        yield 'integer stays unchanged' => [
            'value' => 42,
            'expectedValue' => 42,
        ];

        yield 'boolean stays unchanged' => [
            'value' => true,
            'expectedValue' => true,
        ];

        yield 'null stays unchanged' => [
            'value' => null,
            'expectedValue' => null,
        ];

        yield 'datetime is formatted as atom string' => [
            'value' => new DateTimeImmutable('2026-03-26 12:34:56+00:00'),
            'expectedValue' => '2026-03-26T12:34:56+00:00',
        ];

        yield 'stringable object is cast to string' => [
            'value' => new class() implements Stringable {
                public function __toString(): string
                {
                    return 'stringable-value';
                }
            },
            'expectedValue' => 'stringable-value',
        ];

        yield 'array is encoded as json string' => [
            'value' => ['first' => 1, 'second' => 'two'],
            'expectedValue' => '{"first":1,"second":"two"}',
        ];

        yield 'generic object is encoded as json string' => [
            'value' => (object)['answer' => 42],
            'expectedValue' => '{"answer":42}',
        ];

        yield 'invalid utf8 is substituted during json encoding' => [
            'value' => ['text' => "\xB1\x31"],
            'expectedValue' => '{"text":"�1"}',
        ];
    }
}
