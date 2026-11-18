<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Expression;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Comparison;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\UnsupportedConditionException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\FrameworkBundle\Component\Enum\InvalidEnumCaseException;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\RecordingExpressionBuilder;

final class AbstractExpressionBuilderTest extends TestCase
{
    /**
     * Each operator is compiled with a value of its arity, so the enum and the validation of the values cannot
     * drift apart.
     */
    public function testEveryOperatorOfTheVocabularyIsCompiled(): void
    {
        $expressionOperatorEnum = new ExpressionOperatorEnum();

        foreach ($expressionOperatorEnum->getAllCases() as $operator) {
            $value = $this->createValueOfArity($expressionOperatorEnum->getValueArity($operator));

            $this->assertCompiledInto(
                sprintf('name %s %s', $operator, json_encode($value, JSON_UNESCAPED_UNICODE)),
                new Comparison('name', $operator, $value),
            );
        }
    }

    public function testNestedGroupsAndNegationAreCompiledRecursively(): void
    {
        $this->assertCompiledInto(
            'AND(NOT(name equals "Talíř"), OR(price in [1,2], AND(price greaterThan 50, price lessThan 150)))',
            Condition::andX(
                Condition::not(Condition::equals('name', 'Talíř')),
                Condition::orX(
                    Condition::in('price', [1, 2]),
                    Condition::andX(Condition::greaterThan('price', 50), Condition::lessThan('price', 150)),
                ),
            ),
        );
    }

    /**
     * What an empty group matches is up to the concrete builder, so it is handed over as it is.
     */
    public function testEmptyGroupsAreHandedToTheConcreteBuilder(): void
    {
        $this->assertCompiledInto('AND()', Condition::andX());
        $this->assertCompiledInto('OR()', Condition::orX());
    }

    /**
     * An empty list of values matches nothing and an empty list of excluded values everything, whatever the path
     * leads to.
     */
    public function testEmptyListIsCompiledIntoEmptyGroup(): void
    {
        $this->assertCompiledInto('OR()', Condition::in('name', []));
        $this->assertCompiledInto('AND()', Condition::notIn('name', []));
    }

    public function testConditionOutsideTheVocabularyIsRefused(): void
    {
        $this->expectException(UnsupportedConditionException::class);
        $this->expectExceptionMessage('cannot be compiled by');

        (new RecordingExpressionBuilder())->compile(new TestUnknownCondition());
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
            'bounds given by keys to an operator comparing a range' => [ExpressionOperatorEnum::BETWEEN, ['from' => 50, 'to' => 150]],
            'a missing value in a list' => [ExpressionOperatorEnum::IN, ['Hrnek', null]],
            'a list given to an operator comparing one value' => [ExpressionOperatorEnum::EQUALS, ['Hrnek']],
            'a missing bound of a range' => [ExpressionOperatorEnum::BETWEEN, [50, null]],
            'a list as a bound of a range' => [ExpressionOperatorEnum::BETWEEN, [[50], 150]],
            'a list in a list' => [ExpressionOperatorEnum::IN, [['Hrnek']]],
        ];
    }

    #[DataProvider('mismatchedValueDataProvider')]
    public function testValueNotMatchingTheArityOfTheOperatorIsRefused(string $operator, mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new RecordingExpressionBuilder())->compile(new Comparison('name', $operator, $value));
    }

    public function testUnknownOperatorIsRefused(): void
    {
        $this->expectException(InvalidEnumCaseException::class);

        (new RecordingExpressionBuilder())->compile(new Comparison('name', 'somethingElse', 'Hrnek'));
    }

    private function assertCompiledInto(string $expectedRecord, ConditionInterface $condition): void
    {
        $this->assertSame($expectedRecord, (new RecordingExpressionBuilder())->compile($condition)->record);
    }

    private function createValueOfArity(ExpressionValueArityEnum $arity): mixed
    {
        return match ($arity) {
            ExpressionValueArityEnum::NONE => null,
            ExpressionValueArityEnum::SINGLE => 'Hrnek',
            ExpressionValueArityEnum::LIST => ['Hrnek'],
            ExpressionValueArityEnum::RANGE => ['A', 'Z'],
        };
    }
}

final class TestUnknownCondition implements ConditionInterface
{
}
