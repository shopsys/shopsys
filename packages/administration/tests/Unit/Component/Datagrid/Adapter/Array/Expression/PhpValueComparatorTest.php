<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Array\Expression;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpValueComparator;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\UnsupportedConditionException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\FrameworkBundle\Component\Money\Money;

final class PhpValueComparatorTest extends TestCase
{
    /**
     * @return iterable<string, array{mixed, mixed, int}>
     */
    public static function comparableValuesDataProvider(): iterable
    {
        yield 'two numbers' => [5, 3, 1];

        yield 'an integer and a float' => [1, 1.5, -1];

        yield 'a number stored as text and a number' => ['100', 99, 1];

        yield 'a number and a number stored as text' => [100, '100', 0];

        yield 'a decimal stored as text and a number, to its full precision' => ['1.000000000000000001', 1, 1];

        yield 'two amounts of money' => [Money::create('100.00'), Money::create('100'), 0];

        yield 'money and a number' => [Money::create('99.99'), 100, -1];

        yield 'money and a number stored as text' => [Money::create('100'), '99.5', 1];

        yield 'two texts' => ['abc', 'abd', -1];

        yield 'two texts looking like numbers' => ['0123', '123', -1];

        yield 'two dates' => [new DateTimeImmutable('2026-06-15'), new DateTimeImmutable('2026-01-01'), 1];

        yield 'one moment in two time zones' => [
            new DateTimeImmutable('2026-06-15 12:00', new DateTimeZone('UTC')),
            new DateTimeImmutable('2026-06-15 14:00', new DateTimeZone('Europe/Prague')),
            0,
        ];
    }

    #[DataProvider('comparableValuesDataProvider')]
    public function testComparableValuesAreOrdered(mixed $fieldValue, mixed $value, int $expectedOrder): void
    {
        $comparison = (new PhpValueComparator())->compare($fieldValue, $value, ExpressionOperatorEnum::GREATER_THAN);

        $this->assertSame($expectedOrder, $comparison <=> 0);
    }

    /**
     * @return iterable<string, array{mixed, mixed, string}>
     */
    public static function incomparableValuesDataProvider(): iterable
    {
        yield 'a date and a number' => [new DateTimeImmutable('2026-06-15'), 5, 'DateTimeImmutable'];

        yield 'a number and a date' => [5, new DateTimeImmutable('2026-06-15'), 'DateTimeImmutable'];

        yield 'a date and a text' => [new DateTimeImmutable('2026-06-15'), '2026-01-01', 'DateTimeImmutable'];

        yield 'a boolean and a number' => [true, 1, 'bool'];

        yield 'two booleans' => [true, false, 'bool'];

        yield 'a text that is no number and money' => ['abc', Money::create('1'), 'string'];

        yield 'a number and a text that is no number' => [5, 'abc', 'string'];

        yield 'a number stored as text with spaces and a number' => [' 1', 1, 'string'];
    }

    #[DataProvider('incomparableValuesDataProvider')]
    public function testIncomparableValuesAreRefused(mixed $fieldValue, mixed $value, string $refusedType): void
    {
        $this->expectException(UnsupportedConditionException::class);
        $this->expectExceptionMessage(sprintf('Operator "greaterThan" cannot be applied to a value of type "%s".', $refusedType));

        (new PhpValueComparator())->compare($fieldValue, $value, ExpressionOperatorEnum::GREATER_THAN);
    }

    /**
     * @return iterable<string, array{mixed, mixed, bool}>
     */
    public static function equalityDataProvider(): iterable
    {
        yield 'the same booleans' => [true, true, true];

        yield 'different booleans' => [true, false, false];

        yield 'a number stored as text and the number' => ['100', 100, true];

        yield 'money and money of the same amount' => [Money::create('100.00'), Money::create('100'), true];

        yield 'money and the number of its amount' => [Money::create('100'), 100, true];

        yield 'two texts looking like the same number' => ['0123', '123', false];

        yield 'two texts differing in case' => ['Hrnek', 'hrnek', false];

        yield 'one moment in two time zones' => [
            new DateTimeImmutable('2026-06-15 12:00', new DateTimeZone('UTC')),
            new DateTimeImmutable('2026-06-15 14:00', new DateTimeZone('Europe/Prague')),
            true,
        ];
    }

    #[DataProvider('equalityDataProvider')]
    public function testEquality(mixed $fieldValue, mixed $value, bool $expectedEquality): void
    {
        $this->assertSame($expectedEquality, (new PhpValueComparator())->equals($fieldValue, $value, ExpressionOperatorEnum::EQUALS));
    }

    public function testEqualityOfBooleanAndNumberIsRefused(): void
    {
        $this->expectException(UnsupportedConditionException::class);
        $this->expectExceptionMessage('Operator "equals" cannot be applied to a value of type "bool".');

        (new PhpValueComparator())->equals(true, 1, ExpressionOperatorEnum::EQUALS);
    }

    /**
     * The diacritics of the Latin and the Greek script and of the Cyrillic Ё are stripped, the same letters as
     * PostgreSQL `unaccent` strips.
     */
    public function testTextIsNormalizedToLowerCaseWithoutDiacritics(): void
    {
        $phpValueComparator = new PhpValueComparator();

        $this->assertSame('hrnicek modry', $phpValueComparator->normalize('Hrníček Modrý'));
        $this->assertSame('желтый', $phpValueComparator->normalize('Жёлтый'));
        $this->assertSame('αλφα', $phpValueComparator->normalize('Άλφα'));
        $this->assertSame('йод', $phpValueComparator->normalize('Йод'));
    }
}
