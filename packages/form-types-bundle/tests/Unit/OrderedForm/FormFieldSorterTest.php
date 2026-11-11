<?php

declare(strict_types=1);

namespace Tests\FormTypesBundle\Unit\OrderedForm;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FormTypesBundle\OrderedForm\Exception\InvalidPositionException;
use Shopsys\FormTypesBundle\OrderedForm\Exception\OrderedFormException;
use Shopsys\FormTypesBundle\OrderedForm\FormFieldSorter;

class FormFieldSorterTest extends TestCase
{
    /**
     * @return iterable<string, array{array<string, mixed>, string[]}>
     */
    public static function positionsProvider(): iterable
    {
        yield 'keeps order of not positioned fields' => [
            ['a' => null, 'b' => null],
            ['a', 'b'],
        ];

        yield 'first' => [
            ['a' => null, 'b' => 'first'],
            ['b', 'a'],
        ];

        yield 'last' => [
            ['a' => 'last', 'b' => null],
            ['b', 'a'],
        ];

        yield 'last and first' => [
            ['b' => null, 'c' => 'last', 'a' => 'first'],
            ['a', 'b', 'c'],
        ];

        yield 'weights' => [
            ['b' => 10, 'c' => 20, 'a' => 5],
            ['a', 'b', 'c'],
        ];

        yield 'negative weights' => [
            ['b' => -5, 'c' => 20, 'a' => -10],
            ['a', 'b', 'c'],
        ];

        yield 'weighted fields follow not positioned fields' => [
            ['b' => 1, 'a' => null],
            ['a', 'b'],
        ];

        yield 'all kinds of positions combined' => [
            ['d' => 10, 'a' => 'first', 'c' => 5, 'f' => 'last', 'e' => ['after' => 'd'], 'b' => ['before' => 'c']],
            ['a', 'b', 'c', 'd', 'e', 'f'],
        ];

        yield 'circular relative positions (after and before)' => [
            ['b' => ['after' => 'a'], 'a' => ['before' => 'b']],
            ['a', 'b'],
        ];

        yield 'circular relative positions (before and before)' => [
            ['b' => ['before' => 'a'], 'a' => ['before' => 'b']],
            ['a', 'b'],
        ];

        yield 'keeps order among first and among last fields' => [
            ['c' => 'last', 'd' => 'last', 'a' => 'first', 'b' => 'first'],
            ['a', 'b', 'c', 'd'],
        ];
    }

    /**
     * @param array<string, mixed> $positionsByFieldName
     * @param string[] $expectedOrder
     */
    #[DataProvider('positionsProvider')]
    public function testFieldsAreSortedByPosition(array $positionsByFieldName, array $expectedOrder): void
    {
        $sorter = new FormFieldSorter();

        foreach ($positionsByFieldName as $fieldName => $position) {
            $sorter->add($fieldName, $position);
        }

        self::assertSame($expectedOrder, array_values($sorter->getSortedFieldNames()));
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function invalidPositionsProvider(): iterable
    {
        yield 'unknown string' => [['test' => 'invalid'], '~Invalid value for position~'];

        yield 'boolean' => [['test' => false], '~Invalid value for position~'];

        yield 'array with two entries' => [['test' => ['a', 'b']], '~a position array must only contain exactly one entry~'];

        yield 'empty array' => [['test' => []], '~a position array must only contain exactly one entry~'];

        yield 'unknown direction' => [['test' => ['invalid' => 'before']], '~Unknown direction for position array~'];

        yield 'self reference (before)' => [['test' => ['before' => 'test']], "~A relative positioned form field can't reference itself~"];

        yield 'self reference (after)' => [['test' => ['after' => 'test']], "~A relative positioned form field can't reference itself~"];

        yield 'unknown reference (after)' => [['test' => ['after' => 'unknown']], "~Can't position field '.*?' relative to '.*?', as the field wasn't found\\.~"];

        yield 'unknown reference (before)' => [['test' => ['before' => 'unknown']], "~Can't position field '.*?' relative to '.*?', as the field wasn't found\\.~"];

        yield 'non-string reference' => [['test' => ['before' => 1]], '~must be a string~'];
    }

    /**
     * @param array<string, mixed> $positionsByFieldName
     */
    #[DataProvider('invalidPositionsProvider')]
    public function testInvalidPositionIsRejected(array $positionsByFieldName, string $expectedMessageRegex): void
    {
        $this->expectException(InvalidPositionException::class);
        $this->expectExceptionMessageMatches($expectedMessageRegex);

        $sorter = new FormFieldSorter();

        foreach ($positionsByFieldName as $fieldName => $position) {
            $sorter->add($fieldName, $position);
        }

        $sorter->getSortedFieldNames();
    }

    public function testFieldCannotBeAddedAfterSorting(): void
    {
        $this->expectException(OrderedFormException::class);
        $this->expectExceptionMessage("Can't add item after the form field sorter is frozen.");

        $sorter = new FormFieldSorter();
        $sorter->add('a', null);
        $sorter->getSortedFieldNames();
        $sorter->add('b', null);
    }

    public function testRepeatedSortingReturnsTheSameResult(): void
    {
        $sorter = new FormFieldSorter();
        $sorter->add('a', null);
        $sorter->add('b', 'first');

        self::assertSame($sorter->getSortedFieldNames(), $sorter->getSortedFieldNames());
    }
}
