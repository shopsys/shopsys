<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Array\Expression;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpPathAccessor;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpValueComparator;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Comparison;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotApplicableToValueException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotSupportedException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundInRowException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\FrameworkBundle\Component\Money\Money;

final class PhpExpressionBuilderTest extends TestCase
{
    public function testValueIsReadByDotNotationPath(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'customerUser' => [
                'email' => 'novak@example.com',
            ],
        ];

        $this->assertTrue($expressionBuilder->compile(new Comparison('customerUser.email', ExpressionOperatorEnum::EQUALS, 'novak@example.com'))($row));
        $this->assertFalse($expressionBuilder->compile(new Comparison('customerUser.email', ExpressionOperatorEnum::EQUALS, 'other@example.com'))($row));
    }

    /**
     * An association that is not there has no value below it, which is what a query says as well through
     * its outer join.
     */
    public function testValueBehindAnAbsentAssociationIsNull(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'customerUser' => null,
        ];

        $this->assertTrue($expressionBuilder->compile(new Comparison('customerUser.email', ExpressionOperatorEnum::IS_NULL, null))($row));
        $this->assertFalse($expressionBuilder->compile(new Comparison('customerUser.email', ExpressionOperatorEnum::IS_NOT_NULL, null))($row));
    }

    /**
     * A path addressing nothing is a mistake in the code, so it is refused instead of quietly behaving
     * as a missing value — a query refuses an unknown field as well.
     */
    public function testPathAddressingNothingIsRefused(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();

        $this->expectException(PathNotFoundInRowException::class);
        $this->expectExceptionMessage('Path "custmerUser.email" cannot be read, there is no "custmerUser" in the row. Available: "customerUser".');

        $expressionBuilder->compile(new Comparison('custmerUser.email', ExpressionOperatorEnum::IS_NULL, null))(['customerUser' => null]);
    }

    /**
     * The same semantics a query gets from its subquery — the record matches when at least one of the
     * related rows does.
     */
    public function testPathCrossingCollectionMatchesWhenAnyOfTheValuesDoes(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'domains' => [
                ['domainId' => 1],
                ['domainId' => 3],
            ],
        ];

        $this->assertTrue($expressionBuilder->compile(new Comparison('domains.domainId', ExpressionOperatorEnum::IN, [2, 3]))($row));
        $this->assertFalse($expressionBuilder->compile(new Comparison('domains.domainId', ExpressionOperatorEnum::IN, [2, 4]))($row));
    }

    public function testEmptyCollectionMatchesNothing(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'domains' => [],
        ];

        $this->assertFalse($expressionBuilder->compile(new Comparison('domains.domainId', ExpressionOperatorEnum::IS_NULL, null))($row));
        $this->assertFalse($expressionBuilder->compile(new Comparison('domains.domainId', ExpressionOperatorEnum::IN, [1]))($row));
    }

    /**
     * The same rule the `NORMALIZED()` function of the database applies — "hrnicek" finds "Hrníček" and the
     * other way round.
     */
    public function testTextIsMatchedRegardlessOfCaseAndDiacritics(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'name' => 'Hrníček Modrý',
        ];

        $this->assertTrue($expressionBuilder->compile(new Comparison('name', ExpressionOperatorEnum::CONTAINS, 'MODRÝ'))($row));
        $this->assertTrue($expressionBuilder->compile(new Comparison('name', ExpressionOperatorEnum::CONTAINS, 'hrnicek'))($row));
        $this->assertTrue($expressionBuilder->compile(new Comparison('name', ExpressionOperatorEnum::STARTS_WITH, 'HRNÍČEK'))($row));
        $this->assertTrue($expressionBuilder->compile(new Comparison('name', ExpressionOperatorEnum::ENDS_WITH, 'modry'))($row));
        $this->assertFalse($expressionBuilder->compile(new Comparison('name', ExpressionOperatorEnum::CONTAINS, 'zelený'))($row));
    }

    /**
     * A wildcard is a literal character here, the same way it is escaped in a query.
     */
    public function testWildcardsOfTheSearchedValueAreLiteral(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();

        $this->assertTrue($expressionBuilder->compile(new Comparison('name', ExpressionOperatorEnum::CONTAINS, '100%'))(['name' => 'Sleva 100% dnes']));
        $this->assertFalse($expressionBuilder->compile(new Comparison('name', ExpressionOperatorEnum::CONTAINS, '100%'))(['name' => 'Sleva 100 dnes']));
        $this->assertFalse($expressionBuilder->compile(new Comparison('name', ExpressionOperatorEnum::CONTAINS, 'a_b'))(['name' => 'axb']));
    }

    public function testNumberStoredAsTextIsStillNumber(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'price' => '100',
        ];

        $this->assertTrue($expressionBuilder->compile(new Comparison('price', ExpressionOperatorEnum::EQUALS, 100))($row));
        $this->assertTrue($expressionBuilder->compile(new Comparison('price', ExpressionOperatorEnum::GREATER_THAN, 99))($row));
        $this->assertFalse($expressionBuilder->compile(new Comparison('price', ExpressionOperatorEnum::GREATER_THAN, 100))($row));
        $this->assertTrue($expressionBuilder->compile(new Comparison('price', ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL, 100))($row));
        $this->assertTrue($expressionBuilder->compile(new Comparison('price', ExpressionOperatorEnum::LESS_THAN_OR_EQUAL, 100))($row));
        $this->assertFalse($expressionBuilder->compile(new Comparison('price', ExpressionOperatorEnum::LESS_THAN, 100))($row));
    }

    public function testMoneyIsComparedByItsAmount(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'price' => Money::create('100.00'),
        ];

        $this->assertTrue($expressionBuilder->compile(new Comparison('price', ExpressionOperatorEnum::EQUALS, Money::create('100')))($row));
        $this->assertTrue($expressionBuilder->compile(new Comparison('price', ExpressionOperatorEnum::BETWEEN, [Money::create('50'), Money::create('150')]))($row));
        $this->assertFalse($expressionBuilder->compile(new Comparison('price', ExpressionOperatorEnum::GREATER_THAN, Money::create('100')))($row));
    }

    /**
     * A decimal is compared to its full precision, so two different values of a high precision column
     * never come out equal.
     */
    public function testDecimalIsComparedToItsFullPrecision(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'exchangeRate' => '12345678901234.567891',
        ];

        $this->assertFalse($expressionBuilder->compile(new Comparison('exchangeRate', ExpressionOperatorEnum::EQUALS, '12345678901234.567892'))($row));
        $this->assertTrue($expressionBuilder->compile(new Comparison('exchangeRate', ExpressionOperatorEnum::EQUALS, '12345678901234.567891'))($row));
        $this->assertTrue($expressionBuilder->compile(new Comparison('exchangeRate', ExpressionOperatorEnum::LESS_THAN, '12345678901234.567892'))($row));
    }

    /**
     * Negating a whole expression asks something else than the negating operation of that expression,
     * once the path leads through a collection.
     */
    public function testNegatingAnExpressionOverCollectionDiffersFromTheNegatingOperation(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'items' => [
                ['name' => 'X'],
                ['name' => 'Y'],
            ],
        ];

        // no related row is named X
        $this->assertFalse($expressionBuilder->compile(Condition::not(Condition::equals('items.name', 'X')))($row));
        // some related row is not named X
        $this->assertTrue($expressionBuilder->compile(Condition::notEquals('items.name', 'X'))($row));
    }

    public function testDatesAreComparedChronologically(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'createdAt' => new DateTimeImmutable('2026-06-15'),
        ];

        $this->assertTrue($expressionBuilder->compile(new Comparison('createdAt', ExpressionOperatorEnum::GREATER_THAN, new DateTimeImmutable('2026-01-01')))($row));
        $this->assertTrue($expressionBuilder->compile(new Comparison(
            'createdAt',
            ExpressionOperatorEnum::BETWEEN,
            [new DateTimeImmutable('2026-06-01'), new DateTimeImmutable('2026-06-30')],
        ))($row));
        $this->assertFalse($expressionBuilder->compile(new Comparison('createdAt', ExpressionOperatorEnum::LESS_THAN, new DateTimeImmutable('2026-01-01')))($row));
    }

    public function testNullIsNeverOrdered(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'price' => null,
        ];

        $this->assertFalse($expressionBuilder->compile(new Comparison('price', ExpressionOperatorEnum::GREATER_THAN, 1))($row));
        $this->assertFalse($expressionBuilder->compile(new Comparison('price', ExpressionOperatorEnum::LESS_THAN, 1))($row));
        $this->assertFalse($expressionBuilder->compile(new Comparison('price', ExpressionOperatorEnum::BETWEEN, [0, 2]))($row));
    }

    public function testEmptyListOfValuesMatchesNothing(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'id' => 1,
        ];

        $this->assertFalse($expressionBuilder->compile(new Comparison('id', ExpressionOperatorEnum::IN, []))($row));
        $this->assertTrue($expressionBuilder->compile(new Comparison('id', ExpressionOperatorEnum::NOT_IN, []))($row));
    }

    public function testExpressionsAreCombined(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'name' => 'Hrnek',
            'id' => 5,
        ];

        $this->assertTrue($expressionBuilder->compile(Condition::andX(
            Condition::equals('name', 'Hrnek'),
            Condition::orX(Condition::equals('id', 1), Condition::not(Condition::isNull('id'))),
        ))($row));

        $this->assertFalse($expressionBuilder->compile(Condition::andX(
            Condition::equals('name', 'Hrnek'),
            Condition::equals('id', 1),
        ))($row));
    }

    public function testEmptinessOfCollectionIsDecidedByItsValues(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();

        $this->assertTrue($expressionBuilder->compile(new Comparison('items', ExpressionOperatorEnum::IS_EMPTY, null))(['items' => []]));
        $this->assertFalse($expressionBuilder->compile(new Comparison('items', ExpressionOperatorEnum::IS_EMPTY, null))(['items' => [['name' => 'X']]]));
        $this->assertTrue($expressionBuilder->compile(new Comparison('items', ExpressionOperatorEnum::IS_NOT_EMPTY, null))(['items' => [['name' => 'X']]]));
        $this->assertFalse($expressionBuilder->compile(new Comparison('items', ExpressionOperatorEnum::IS_NOT_EMPTY, null))(['items' => []]));
    }

    /**
     * A database refuses to search text in a date or a number, so does this medium — instead of quietly
     * matching nothing while the query would fail.
     */
    public function testTextOperationOnNonTextValueIsRefused(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();

        $this->expectException(OperatorNotApplicableToValueException::class);
        $this->expectExceptionMessage('Operator "contains" cannot be applied to a value of type "int".');

        $expressionBuilder->compile(new Comparison('price', ExpressionOperatorEnum::CONTAINS, '10'))(['price' => 100]);
    }

    public function testTextOperationOnMissingValueMatchesNothing(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();

        $this->assertFalse($expressionBuilder->compile(new Comparison('name', ExpressionOperatorEnum::CONTAINS, 'a'))(['name' => null]));
    }

    public function testNoExpressionAtAllMatchesEverything(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();

        $this->assertTrue($expressionBuilder->compile(Condition::andX())([]));
        $this->assertFalse($expressionBuilder->compile(Condition::orX())([]));
    }

    /**
     * A record without a value never matches a comparison, but always matches its negation — the same answer
     * the query of the ORM adapter gives.
     */
    public function testMissingValueMatchesEveryNegationButNoComparison(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'name' => null,
        ];

        $this->assertFalse($expressionBuilder->compile(Condition::equals('name', 'Hrnek'))($row));
        $this->assertFalse($expressionBuilder->compile(Condition::in('name', ['Hrnek']))($row));
        $this->assertFalse($expressionBuilder->compile(Condition::greaterThan('name', 'A'))($row));
        $this->assertTrue($expressionBuilder->compile(Condition::notEquals('name', 'Hrnek'))($row));
        $this->assertTrue($expressionBuilder->compile(Condition::notIn('name', ['Hrnek']))($row));
        $this->assertTrue($expressionBuilder->compile(Condition::not(Condition::equals('name', 'Hrnek')))($row));
        $this->assertTrue($expressionBuilder->compile(Condition::not(Condition::greaterThan('name', 'A')))($row));
    }

    /**
     * @return array<string, array{string, string, mixed, bool}>
     */
    public static function operatorDataProvider(): array
    {
        return [
            ExpressionOperatorEnum::EQUALS => [ExpressionOperatorEnum::EQUALS, 'name', 'Hrnek', true],
            ExpressionOperatorEnum::NOT_EQUALS => [ExpressionOperatorEnum::NOT_EQUALS, 'name', 'Hrnek', false],
            ExpressionOperatorEnum::CONTAINS => [ExpressionOperatorEnum::CONTAINS, 'name', 'rne', true],
            ExpressionOperatorEnum::STARTS_WITH => [ExpressionOperatorEnum::STARTS_WITH, 'name', 'Hr', true],
            ExpressionOperatorEnum::ENDS_WITH => [ExpressionOperatorEnum::ENDS_WITH, 'name', 'ek', true],
            ExpressionOperatorEnum::GREATER_THAN => [ExpressionOperatorEnum::GREATER_THAN, 'price', 99, true],
            ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL => [ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL, 'price', 100, true],
            ExpressionOperatorEnum::LESS_THAN => [ExpressionOperatorEnum::LESS_THAN, 'price', 101, true],
            ExpressionOperatorEnum::LESS_THAN_OR_EQUAL => [ExpressionOperatorEnum::LESS_THAN_OR_EQUAL, 'price', 100, true],
            ExpressionOperatorEnum::BETWEEN => [ExpressionOperatorEnum::BETWEEN, 'price', [50, 150], true],
            ExpressionOperatorEnum::IN => [ExpressionOperatorEnum::IN, 'name', ['Hrnek', 'Talíř'], true],
            ExpressionOperatorEnum::NOT_IN => [ExpressionOperatorEnum::NOT_IN, 'name', ['Talíř'], true],
            ExpressionOperatorEnum::IS_NULL => [ExpressionOperatorEnum::IS_NULL, 'name', null, false],
            ExpressionOperatorEnum::IS_NOT_NULL => [ExpressionOperatorEnum::IS_NOT_NULL, 'name', null, true],
            ExpressionOperatorEnum::IS_EMPTY => [ExpressionOperatorEnum::IS_EMPTY, 'items', null, false],
            ExpressionOperatorEnum::IS_NOT_EMPTY => [ExpressionOperatorEnum::IS_NOT_EMPTY, 'items', null, true],
        ];
    }

    #[DataProvider('operatorDataProvider')]
    public function testOperatorIsExpressedAsCallback(
        string $operator,
        string $path,
        mixed $value,
        bool $expectedMatch,
    ): void {
        $row = [
            'name' => 'Hrnek',
            'price' => 100,
            'items' => [['name' => 'X']],
        ];

        $this->assertSame($expectedMatch, $this->createExpressionBuilder()->compile(new Comparison($path, $operator, $value))($row));
    }

    /**
     * The vocabulary and the builder must not drift apart — every case the enum lists has a branch in `compare()`.
     */
    public function testEveryOperatorOfTheVocabularyIsExpressed(): void
    {
        $this->assertEqualsCanonicalizing((new ExpressionOperatorEnum())->getAllCases(), array_keys(self::operatorDataProvider()));
    }

    /**
     * A project adds an operation to the vocabulary by a subclass of the enum; a medium it did not teach the
     * operation refuses it.
     */
    public function testOperationTheMediumDoesNotExpressIsRefused(): void
    {
        $expressionOperatorEnum = new class() extends ExpressionOperatorEnum {
            public const string JSON_CONTAINS = 'jsonContains';
        };
        $expressionBuilder = new PhpExpressionBuilder(new PhpPathAccessor(), new PhpValueComparator(), $expressionOperatorEnum);

        $this->expectException(OperatorNotSupportedException::class);

        $expressionBuilder->compile(new Comparison('name', 'jsonContains', 'Hrnek'));
    }

    public function testConditionTreeIsCompiledIntoCallback(): void
    {
        $matchesRow = $this->createExpressionBuilder()->compile(Condition::andX(
            Condition::not(Condition::equals('name', 'Talíř')),
            Condition::between('price', 50, 150),
        ));

        $this->assertTrue($matchesRow(['name' => 'Hrnek', 'price' => 100]));
        $this->assertFalse($matchesRow(['name' => 'Talíř', 'price' => 100]));
    }

    private function createExpressionBuilder(): PhpExpressionBuilder
    {
        return new PhpExpressionBuilder(new PhpPathAccessor(), new PhpValueComparator(), new ExpressionOperatorEnum());
    }
}
