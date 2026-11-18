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
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\UnsupportedConditionException;
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
     * A row of a query or of a grid holds the value of a related record under the whole dot notation key.
     */
    public function testValueIsReadByWholeDotNotationKey(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'customerUser.email' => 'novak@example.com',
            'items' => [
                ['product.catnum' => '123'],
            ],
        ];

        $this->assertTrue($expressionBuilder->compile(new Comparison('customerUser.email', ExpressionOperatorEnum::EQUALS, 'novak@example.com'))($row));
        $this->assertTrue($expressionBuilder->compile(new Comparison('items.product.catnum', ExpressionOperatorEnum::EQUALS, '123'))($row));
        $this->assertFalse($expressionBuilder->compile(new Comparison('customerUser.email', ExpressionOperatorEnum::IS_NULL, null))($row));
    }

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
     * as a missing value.
     */
    public function testPathAddressingNothingIsRefused(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();

        $this->expectException(UnsupportedConditionException::class);
        $this->expectExceptionMessage('Path "custmerUser.email" cannot be read, there is no "custmerUser" in the row. Available: "customerUser".');

        $expressionBuilder->compile(new Comparison('custmerUser.email', ExpressionOperatorEnum::IS_NULL, null))(['customerUser' => null]);
    }

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

    public function testNumericTextsAreComparedAsTexts(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'catnum' => '0123',
        ];

        $this->assertFalse($expressionBuilder->compile(new Comparison('catnum', ExpressionOperatorEnum::EQUALS, '123'))($row));
        $this->assertFalse($expressionBuilder->compile(new Comparison('catnum', ExpressionOperatorEnum::IN, ['123', '00123']))($row));
        $this->assertTrue($expressionBuilder->compile(new Comparison('catnum', ExpressionOperatorEnum::EQUALS, '0123'))($row));
        $this->assertTrue($expressionBuilder->compile(new Comparison('catnum', ExpressionOperatorEnum::LESS_THAN, '1'))($row));
    }

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

    public function testRelatedRowWithoutValueDoesNotMakeCollectionNonEmpty(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'items' => [
                ['product' => null],
            ],
        ];

        $this->assertTrue($expressionBuilder->compile(new Comparison('items.product', ExpressionOperatorEnum::IS_EMPTY, null))($row));
        $this->assertFalse($expressionBuilder->compile(new Comparison('items.product', ExpressionOperatorEnum::IS_NOT_EMPTY, null))($row));
        $this->assertTrue($expressionBuilder->compile(new Comparison('items.product.catnum', ExpressionOperatorEnum::IS_EMPTY, null))($row));
    }

    public function testTextOperationOnNonTextValueIsRefused(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();

        $this->expectException(UnsupportedConditionException::class);
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

    public function testMissingValueMatchesEveryNegationButNoComparison(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'name' => null,
        ];

        $this->assertFalse($expressionBuilder->compile(Condition::equals('name', 'Hrnek'))($row));
        $this->assertFalse($expressionBuilder->compile(Condition::in('name', ['Hrnek']))($row));
        $this->assertFalse($expressionBuilder->compile(Condition::greaterThan('name', 'A'))($row));
        $this->assertFalse($expressionBuilder->compile(Condition::between('name', 'A', 'Z'))($row));
        $this->assertFalse($expressionBuilder->compile(Condition::contains('name', 'a'))($row));
        $this->assertTrue($expressionBuilder->compile(Condition::notEquals('name', 'Hrnek'))($row));
        $this->assertTrue($expressionBuilder->compile(Condition::notIn('name', ['Hrnek']))($row));
        $this->assertTrue($expressionBuilder->compile(Condition::not(Condition::equals('name', 'Hrnek')))($row));
        $this->assertTrue($expressionBuilder->compile(Condition::not(Condition::greaterThan('name', 'A')))($row));
        $this->assertTrue($expressionBuilder->compile(Condition::not(Condition::between('name', 'A', 'Z')))($row));
        $this->assertTrue($expressionBuilder->compile(Condition::not(Condition::contains('name', 'a')))($row));
    }

    /**
     * Values that cannot be compared fail the comparison, so its negation does not match every record instead.
     */
    public function testNegatedComparisonOfIncomparableValuesIsRefused(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();

        $this->expectException(UnsupportedConditionException::class);
        $this->expectExceptionMessage('Operator "greaterThan" cannot be applied to a value of type "DateTimeImmutable".');

        $expressionBuilder->compile(Condition::not(Condition::greaterThan('createdAt', 5)))(['createdAt' => new DateTimeImmutable('2026-06-15')]);
    }

    /**
     * Both bounds of a range are compared even when the first one already decides, otherwise the negation would match
     * every record instead of failing.
     */
    public function testRangeWithIncomparableBoundIsRefused(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();

        $this->expectException(UnsupportedConditionException::class);

        $expressionBuilder->compile(Condition::not(Condition::between('price', 200, new DateTimeImmutable('2026-06-15'))))(['price' => 100]);
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
            'greaterThan the value itself' => [ExpressionOperatorEnum::GREATER_THAN, 'price', 100, false],
            ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL => [ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL, 'price', 100, true],
            ExpressionOperatorEnum::LESS_THAN => [ExpressionOperatorEnum::LESS_THAN, 'price', 101, true],
            'lessThan the value itself' => [ExpressionOperatorEnum::LESS_THAN, 'price', 100, false],
            ExpressionOperatorEnum::LESS_THAN_OR_EQUAL => [ExpressionOperatorEnum::LESS_THAN_OR_EQUAL, 'price', 100, true],
            ExpressionOperatorEnum::BETWEEN => [ExpressionOperatorEnum::BETWEEN, 'price', [50, 150], true],
            'between bounds equal to the value' => [ExpressionOperatorEnum::BETWEEN, 'price', [100, 100], true],
            'between bounds below the value' => [ExpressionOperatorEnum::BETWEEN, 'price', [50, 99], false],
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
     * `operatorDataProvider()` has to list every operator of the enum, so none can miss its branch in `compare()`.
     */
    public function testEveryOperatorOfTheVocabularyIsExpressed(): void
    {
        $this->assertEqualsCanonicalizing((new ExpressionOperatorEnum())->getAllCases(), array_values(array_unique(array_column(self::operatorDataProvider(), 0))));
    }

    /**
     * An operation a project adds to the enum fails until the builder is extended to express it.
     */
    public function testOperationTheBuilderDoesNotExpressIsRefused(): void
    {
        $expressionOperatorEnum = new class() extends ExpressionOperatorEnum {
            public const string JSON_CONTAINS = 'jsonContains';
        };
        $expressionBuilder = new PhpExpressionBuilder(new PhpPathAccessor(), new PhpValueComparator(), $expressionOperatorEnum);

        $this->expectException(UnsupportedConditionException::class);
        $this->expectExceptionMessage('Operator "jsonContains" is not expressed by');

        $expressionBuilder->compile(new Comparison('name', 'jsonContains', 'Hrnek'));
    }

    private function createExpressionBuilder(): PhpExpressionBuilder
    {
        return new PhpExpressionBuilder(new PhpPathAccessor(), new PhpValueComparator(), new ExpressionOperatorEnum());
    }
}
