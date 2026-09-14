<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Expression;

use Closure;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpPathAccessor;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpValueComparator;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Comparison;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\MediumSpecificConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ConditionNotSupportedException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotSupportedException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionCompiler;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\FrameworkBundle\Component\Enum\InvalidEnumCaseException;

final class ExpressionCompilerTest extends TestCase
{
    /**
     * @return array<string, array{string, mixed, array<string, mixed>, bool}>
     */
    public static function operatorDataProvider(): array
    {
        $row = [
            'name' => 'Hrnek',
            'price' => 100,
        ];

        return [
            ExpressionOperatorEnum::EQUALS => [ExpressionOperatorEnum::EQUALS, 'Hrnek', $row, true],
            ExpressionOperatorEnum::NOT_EQUALS => [ExpressionOperatorEnum::NOT_EQUALS, 'Hrnek', $row, false],
            ExpressionOperatorEnum::CONTAINS => [ExpressionOperatorEnum::CONTAINS, 'rne', $row, true],
            ExpressionOperatorEnum::STARTS_WITH => [ExpressionOperatorEnum::STARTS_WITH, 'Hr', $row, true],
            ExpressionOperatorEnum::ENDS_WITH => [ExpressionOperatorEnum::ENDS_WITH, 'ek', $row, true],
            ExpressionOperatorEnum::IS_NULL => [ExpressionOperatorEnum::IS_NULL, null, $row, false],
            ExpressionOperatorEnum::IS_NOT_NULL => [ExpressionOperatorEnum::IS_NOT_NULL, null, $row, true],
            ExpressionOperatorEnum::IN => [ExpressionOperatorEnum::IN, ['Hrnek', 'Talíř'], $row, true],
            ExpressionOperatorEnum::NOT_IN => [ExpressionOperatorEnum::NOT_IN, ['Talíř'], $row, true],
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    #[DataProvider('operatorDataProvider')]
    public function testComparisonIsCompiledIntoTheMatchingOperation(
        string $operator,
        mixed $value,
        array $row,
        bool $expectedMatch,
    ): void {
        $matchesRow = $this->compile(new Comparison('name', $operator, $value));

        $this->assertSame($expectedMatch, $matchesRow($row));
    }

    /**
     * @return array<string, array{string, mixed}>
     */
    public static function comparisonOperatorDataProvider(): array
    {
        return [
            ExpressionOperatorEnum::GREATER_THAN => [ExpressionOperatorEnum::GREATER_THAN, 99],
            ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL => [ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL, 100],
            ExpressionOperatorEnum::LESS_THAN => [ExpressionOperatorEnum::LESS_THAN, 101],
            ExpressionOperatorEnum::LESS_THAN_OR_EQUAL => [ExpressionOperatorEnum::LESS_THAN_OR_EQUAL, 100],
        ];
    }

    #[DataProvider('comparisonOperatorDataProvider')]
    public function testComparingOperatorIsCompiledIntoTheMatchingOperation(string $operator, mixed $value): void
    {
        $matchesRow = $this->compile(new Comparison('price', $operator, $value));

        $this->assertTrue($matchesRow(['price' => 100]));
    }

    public function testRangeOperatorTakesItsBoundsAsList(): void
    {
        $matchesRow = $this->compile(Condition::between('price', 50, 150));

        $this->assertTrue($matchesRow(['price' => 100]));
        $this->assertFalse($matchesRow(['price' => 200]));
    }

    /**
     * Every operation of the vocabulary has to be compilable, otherwise the vocabulary and the builder
     * would be free to drift apart.
     */
    public function testEveryOperatorOfTheVocabularyIsCompiled(): void
    {
        $expressionOperatorEnum = new ExpressionOperatorEnum();

        foreach ($expressionOperatorEnum->getAllCases() as $operator) {
            $expression = $this->createCompiler()->compile(
                $this->createExpressionBuilder(),
                new Comparison('name', $operator, $this->createValueOfArity($expressionOperatorEnum->getValueArity($operator))),
            );

            $this->assertInstanceOf(Closure::class, $expression, $operator);
        }
    }

    public function testConjunctionMatchesWhenEveryConditionMatches(): void
    {
        $matchesRow = $this->compile(Condition::andX(
            Condition::contains('name', 'hrnek'),
            Condition::greaterThan('price', 50),
        ));

        $this->assertTrue($matchesRow(['name' => 'Hrnek', 'price' => 100]));
        $this->assertFalse($matchesRow(['name' => 'Hrnek', 'price' => 10]));
    }

    public function testDisjunctionMatchesWhenSomeConditionMatches(): void
    {
        $matchesRow = $this->compile(Condition::orX(
            Condition::contains('name', 'talíř'),
            Condition::greaterThan('price', 50),
        ));

        $this->assertTrue($matchesRow(['name' => 'Hrnek', 'price' => 100]));
        $this->assertFalse($matchesRow(['name' => 'Hrnek', 'price' => 10]));
    }

    public function testNestedGroupsAndNegationAreCompiledRecursively(): void
    {
        $matchesRow = $this->compile(Condition::andX(
            Condition::not(Condition::equals('name', 'Talíř')),
            Condition::orX(
                Condition::in('price', [1, 2]),
                Condition::andX(Condition::greaterThan('price', 50), Condition::lessThan('price', 150)),
            ),
        ));

        $this->assertTrue($matchesRow(['name' => 'Hrnek', 'price' => 100]));
        $this->assertFalse($matchesRow(['name' => 'Talíř', 'price' => 100]));
        $this->assertFalse($matchesRow(['name' => 'Hrnek', 'price' => 200]));
    }

    public function testEmptyGroupsFollowTheSemanticsOfTheBuilder(): void
    {
        $this->assertTrue($this->compile(Condition::andX())(['name' => 'Hrnek']));
        $this->assertFalse($this->compile(Condition::orX())(['name' => 'Hrnek']));
    }

    /**
     * A medium-specific condition knows the builder it needs, so the compiler leaves it to compile itself.
     */
    public function testMediumSpecificConditionCompilesItself(): void
    {
        $expressionBuilder = $this->createExpressionBuilder();
        $condition = new TestMediumSpecificCondition(static fn (array $row): bool => $row['name'] === 'Hrnek');

        $matchesRow = $this->createCompiler()->compile($expressionBuilder, Condition::andX($condition, Condition::greaterThan('price', 50)));

        $this->assertSame($expressionBuilder, $condition->receivedExpressionBuilder);
        $this->assertTrue($matchesRow(['name' => 'Hrnek', 'price' => 100]));
        $this->assertFalse($matchesRow(['name' => 'Talíř', 'price' => 100]));
    }

    public function testConditionOutsideTheVocabularyIsRefused(): void
    {
        $this->expectException(ConditionNotSupportedException::class);

        $this->createCompiler()->compile($this->createExpressionBuilder(), new TestUnknownCondition());
    }

    /**
     * @return array<string, array{string, mixed}>
     */
    public static function mismatchedValueDataProvider(): array
    {
        return [
            'a value given to an operator comparing nothing' => [ExpressionOperatorEnum::IS_NULL, 'Hrnek'],
            'no value given to an operator comparing one' => [ExpressionOperatorEnum::EQUALS, null],
            'a single value given to an operator comparing a list' => [ExpressionOperatorEnum::IN, 'Hrnek'],
            'a single value given to an operator comparing a range' => [ExpressionOperatorEnum::BETWEEN, 50],
            'one bound given to an operator comparing a range' => [ExpressionOperatorEnum::BETWEEN, [50]],
        ];
    }

    #[DataProvider('mismatchedValueDataProvider')]
    public function testValueNotMatchingTheArityOfTheOperatorIsRefused(string $operator, mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createCompiler()->compile($this->createExpressionBuilder(), new Comparison('name', $operator, $value));
    }

    public function testUnknownOperatorIsRefused(): void
    {
        $this->expectException(InvalidEnumCaseException::class);

        $this->createCompiler()->compile($this->createExpressionBuilder(), new Comparison('name', 'somethingElse', 'Hrnek'));
    }

    public function testOperatorTheMediumCannotEvaluateIsRefused(): void
    {
        $expressionBuilderStub = $this->createStub(ExpressionBuilderInterface::class);
        $expressionBuilderStub->method('supportsOperator')->willReturn(false);

        $this->expectException(OperatorNotSupportedException::class);

        $this->createCompiler()->compile($expressionBuilderStub, Condition::equals('name', 'Hrnek'));
    }

    private function createValueOfArity(string $arity): mixed
    {
        return match ($arity) {
            ExpressionValueArityEnum::NONE => null,
            ExpressionValueArityEnum::LIST => ['Hrnek'],
            ExpressionValueArityEnum::RANGE => ['A', 'Z'],
            default => 'Hrnek',
        };
    }

    /**
     * @return \Closure(array<string, mixed> $row): bool
     */
    private function compile(ConditionInterface $condition): Closure
    {
        $expression = $this->createCompiler()->compile($this->createExpressionBuilder(), $condition);
        $this->assertInstanceOf(Closure::class, $expression);

        return $expression;
    }

    private function createCompiler(): ExpressionCompiler
    {
        return new ExpressionCompiler(new ExpressionOperatorEnum());
    }

    private function createExpressionBuilder(): PhpExpressionBuilder
    {
        return new PhpExpressionBuilder(new PhpPathAccessor(), new PhpValueComparator());
    }
}

final class TestMediumSpecificCondition implements MediumSpecificConditionInterface
{
    public ?ExpressionBuilderInterface $receivedExpressionBuilder = null;

    public function __construct(
        private readonly Closure $matchesRow,
    ) {
    }

    #[Override]
    public function compile(ExpressionBuilderInterface $expressionBuilder): Closure
    {
        $this->receivedExpressionBuilder = $expressionBuilder;

        return $this->matchesRow;
    }
}

final class TestUnknownCondition implements ConditionInterface
{
}
