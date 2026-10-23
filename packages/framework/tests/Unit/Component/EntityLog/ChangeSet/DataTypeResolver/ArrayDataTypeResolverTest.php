<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\EntityLog\ChangeSet\DataTypeResolver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver\ArrayDataTypeResolver;

final class ArrayDataTypeResolverTest extends TestCase
{
    /**
     * @return iterable<string, array{changes: array{0: mixed, 1: mixed}, isResolved: bool}>
     */
    public static function getResolvedDataTypeData(): iterable
    {
        yield 'array changed to another array' => [
            'changes' => [[1], [2]],
            'isResolved' => true,
        ];

        yield 'array set from null' => ['changes' => [null, [1]], 'isResolved' => true];

        yield 'array changed to null' => ['changes' => [[1], null], 'isResolved' => true];

        yield 'scalar is left to other resolvers' => ['changes' => ['1', '2'], 'isResolved' => false];

        yield 'null to null is not resolved' => ['changes' => [null, null], 'isResolved' => false];
    }

    /**
     * @param array{0: mixed, 1: mixed} $changes
     */
    #[DataProvider('getResolvedDataTypeData')]
    public function testResolvesOnlyArrayChanges(array $changes, bool $isResolved): void
    {
        $arrayDataTypeResolver = new ArrayDataTypeResolver();

        $isResolvedDataType = $arrayDataTypeResolver->isResolvedDataTypeByChanges($changes);

        $this->assertSame($isResolved, $isResolvedDataType);
    }

    /**
     * @return iterable<string, array{value: array<mixed>, expectedReadableValue: string}>
     */
    public static function getReadableValueData(): iterable
    {
        yield 'list' => ['value' => [1, 2, 5], 'expectedReadableValue' => '[1,2,5]'];

        yield 'keys are kept' => ['value' => ['color' => 'red', 'size' => 'XL'], 'expectedReadableValue' => '{"color":"red","size":"XL"}'];

        yield 'empty array' => ['value' => [], 'expectedReadableValue' => '[]'];

        yield 'nested array' => ['value' => ['a' => ['b' => 1]], 'expectedReadableValue' => '{"a":{"b":1}}'];

        yield 'non-ASCII characters are not escaped' => ['value' => ['name' => ['Zásilkovna']], 'expectedReadableValue' => '{"name":["Zásilkovna"]}'];
    }

    /**
     * @param array<mixed> $value
     */
    #[DataProvider('getReadableValueData')]
    public function testReadableValueOfNewArray(array $value, string $expectedReadableValue): void
    {
        $arrayDataTypeResolver = new ArrayDataTypeResolver();

        $resolvedChanges = $arrayDataTypeResolver->getResolvedChanges([null, $value]);

        $this->assertNull($resolvedChanges->oldReadableValue);
        $this->assertSame($expectedReadableValue, $resolvedChanges->newReadableValue);
        $this->assertSame($value, $resolvedChanges->newValue);
    }

    public function testSameArrayIsNotChange(): void
    {
        $arrayDataTypeResolver = new ArrayDataTypeResolver();

        $resolvedChanges = $arrayDataTypeResolver->getResolvedChanges([[1, 2], [1, 2]]);

        $this->assertTrue($resolvedChanges->isOldValueSameAsNewValue());
    }

    public function testDifferentArrayIsChange(): void
    {
        $arrayDataTypeResolver = new ArrayDataTypeResolver();

        $resolvedChanges = $arrayDataTypeResolver->getResolvedChanges([[1, 2], [1, 2, 3]]);

        $this->assertFalse($resolvedChanges->isOldValueSameAsNewValue());
        $this->assertSame('[1,2]', $resolvedChanges->oldReadableValue);
        $this->assertSame('[1,2,3]', $resolvedChanges->newReadableValue);
    }
}
