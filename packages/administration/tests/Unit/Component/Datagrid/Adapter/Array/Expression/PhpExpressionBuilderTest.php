<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Array\Expression;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpPathAccessor;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpValueComparator;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ExpressionFromForeignMediumException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotApplicableToValueException;
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

        $this->assertTrue($expressionBuilder->equals('customerUser.email', 'novak@example.com')($row));
        $this->assertFalse($expressionBuilder->equals('customerUser.email', 'other@example.com')($row));
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

        $this->assertTrue($expressionBuilder->isNull('customerUser.email')($row));
        $this->assertFalse($expressionBuilder->isNotNull('customerUser.email')($row));
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

        $expressionBuilder->isNull('custmerUser.email')(['customerUser' => null]);
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

        $this->assertTrue($expressionBuilder->in('domains.domainId', [2, 3])($row));
        $this->assertFalse($expressionBuilder->in('domains.domainId', [2, 4])($row));
    }

    public function testEmptyCollectionMatchesNothing(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'domains' => [],
        ];

        $this->assertFalse($expressionBuilder->isNull('domains.domainId')($row));
        $this->assertFalse($expressionBuilder->in('domains.domainId', [1])($row));
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

        $this->assertTrue($expressionBuilder->contains('name', 'MODRÝ')($row));
        $this->assertTrue($expressionBuilder->contains('name', 'hrnicek')($row));
        $this->assertTrue($expressionBuilder->startsWith('name', 'HRNÍČEK')($row));
        $this->assertTrue($expressionBuilder->endsWith('name', 'modry')($row));
        $this->assertFalse($expressionBuilder->contains('name', 'zelený')($row));
    }

    /**
     * A wildcard is a literal character here, the same way it is escaped in a query.
     */
    public function testWildcardsOfTheSearchedValueAreLiteral(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();

        $this->assertTrue($expressionBuilder->contains('name', '100%')(['name' => 'Sleva 100% dnes']));
        $this->assertFalse($expressionBuilder->contains('name', '100%')(['name' => 'Sleva 100 dnes']));
        $this->assertFalse($expressionBuilder->contains('name', 'a_b')(['name' => 'axb']));
    }

    public function testNumberStoredAsTextIsStillNumber(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'price' => '100',
        ];

        $this->assertTrue($expressionBuilder->equals('price', 100)($row));
        $this->assertTrue($expressionBuilder->greaterThan('price', 99)($row));
        $this->assertFalse($expressionBuilder->greaterThan('price', 100)($row));
        $this->assertTrue($expressionBuilder->greaterThanOrEqual('price', 100)($row));
        $this->assertTrue($expressionBuilder->lessThanOrEqual('price', 100)($row));
        $this->assertFalse($expressionBuilder->lessThan('price', 100)($row));
    }

    public function testMoneyIsComparedByItsAmount(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'price' => Money::create('100.00'),
        ];

        $this->assertTrue($expressionBuilder->equals('price', Money::create('100'))($row));
        $this->assertTrue($expressionBuilder->between('price', Money::create('50'), Money::create('150'))($row));
        $this->assertFalse($expressionBuilder->greaterThan('price', Money::create('100'))($row));
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

        $this->assertFalse($expressionBuilder->equals('exchangeRate', '12345678901234.567892')($row));
        $this->assertTrue($expressionBuilder->equals('exchangeRate', '12345678901234.567891')($row));
        $this->assertTrue($expressionBuilder->lessThan('exchangeRate', '12345678901234.567892')($row));
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
        $this->assertFalse($expressionBuilder->not($expressionBuilder->equals('items.name', 'X'))($row));
        // some related row is not named X
        $this->assertTrue($expressionBuilder->notEquals('items.name', 'X')($row));
    }

    public function testDatesAreComparedChronologically(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'createdAt' => new DateTimeImmutable('2026-06-15'),
        ];

        $this->assertTrue($expressionBuilder->greaterThan('createdAt', new DateTimeImmutable('2026-01-01'))($row));
        $this->assertTrue($expressionBuilder->between(
            'createdAt',
            new DateTimeImmutable('2026-06-01'),
            new DateTimeImmutable('2026-06-30'),
        )($row));
        $this->assertFalse($expressionBuilder->lessThan('createdAt', new DateTimeImmutable('2026-01-01'))($row));
    }

    public function testNullIsNeverOrdered(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'price' => null,
        ];

        $this->assertFalse($expressionBuilder->greaterThan('price', 1)($row));
        $this->assertFalse($expressionBuilder->lessThan('price', 1)($row));
        $this->assertFalse($expressionBuilder->between('price', 0, 2)($row));
    }

    public function testEmptyListOfValuesMatchesNothing(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'id' => 1,
        ];

        $this->assertFalse($expressionBuilder->in('id', [])($row));
        $this->assertTrue($expressionBuilder->notIn('id', [])($row));
    }

    public function testExpressionsAreCombined(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $row = [
            'name' => 'Hrnek',
            'id' => 5,
        ];

        $this->assertTrue($expressionBuilder->andX(
            $expressionBuilder->equals('name', 'Hrnek'),
            $expressionBuilder->orX(
                $expressionBuilder->equals('id', 1),
                $expressionBuilder->not($expressionBuilder->isNull('id')),
            ),
        )($row));

        $this->assertFalse($expressionBuilder->andX(
            $expressionBuilder->equals('name', 'Hrnek'),
            $expressionBuilder->equals('id', 1),
        )($row));
    }

    public function testEmptinessOfCollectionIsDecidedByItsValues(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();

        $this->assertTrue($expressionBuilder->isEmpty('items')(['items' => []]));
        $this->assertFalse($expressionBuilder->isEmpty('items')(['items' => [['name' => 'X']]]));
        $this->assertTrue($expressionBuilder->isNotEmpty('items')(['items' => [['name' => 'X']]]));
        $this->assertFalse($expressionBuilder->isNotEmpty('items')(['items' => []]));
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

        $expressionBuilder->contains('price', '10')(['price' => 100]);
    }

    public function testTextOperationOnMissingValueMatchesNothing(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();

        $this->assertFalse($expressionBuilder->contains('name', 'a')(['name' => null]));
    }

    public function testNoExpressionAtAllMatchesEverything(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();

        $this->assertTrue($expressionBuilder->andX()([]));
        $this->assertFalse($expressionBuilder->orX()([]));
    }

    public function testExpressionOfAnotherMediumIsRefused(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();

        $this->expectException(ExpressionFromForeignMediumException::class);

        // the static analysis refuses this as well, which is the first line of defence
        /** @phpstan-ignore argument.type */
        $expressionBuilder->not(new DateTimeImmutable());
    }

    public function testEveryOperatorOfTheVocabularyIsSupported(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();

        foreach ((new ExpressionOperatorEnum())->getAllCases() as $operator) {
            $this->assertTrue($expressionBuilder->supportsOperator($operator), $operator);
        }
    }

    private function createExpressionBuilder(): PhpExpressionBuilder
    {
        return new PhpExpressionBuilder(new PhpPathAccessor(), new PhpValueComparator());
    }
}
