<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Expression;

use DateTimeImmutable;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Comparison;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\MediumSpecificConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ConditionNotSupportedException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ExpressionFromForeignMediumException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\FrameworkBundle\Component\Enum\InvalidEnumCaseException;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\RecordedExpression;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\RecordingExpressionBuilder;

final class AbstractExpressionBuilderTest extends TestCase
{
    public function testComparisonIsCompiledIntoTheComparisonOfTheMedium(): void
    {
        $this->assertCompiledInto('name contains "rne"', Condition::contains('name', 'rne'));
        $this->assertCompiledInto('price between [50,150]', Condition::between('price', 50, 150));
        $this->assertCompiledInto('id in [1,2]', Condition::in('id', [1, 2]));
        $this->assertCompiledInto('email isNull null', Condition::isNull('email'));
    }

    /**
     * Every operation of the vocabulary has to be compilable with a value of its arity, otherwise the
     * vocabulary and the validation of the values would be free to drift apart.
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
     * What an empty group matches is up to the medium, so it is handed over as it is.
     */
    public function testEmptyGroupsAreHandedToTheMedium(): void
    {
        $this->assertCompiledInto('AND()', Condition::andX());
        $this->assertCompiledInto('OR()', Condition::orX());
    }

    /**
     * A medium-specific condition knows the builder it needs, so the builder leaves it to compile itself.
     */
    public function testMediumSpecificConditionCompilesItself(): void
    {
        $expressionBuilder = new RecordingExpressionBuilder();
        $condition = new TestMediumSpecificCondition(new RecordedExpression('native'));

        $expression = $expressionBuilder->compile(Condition::andX($condition, Condition::greaterThan('price', 50)));

        $this->assertSame($expressionBuilder, $condition->receivedExpressionBuilder);
        $this->assertSame('AND(native, price greaterThan 50)', $expression->record);
    }

    public function testMediumSpecificConditionReturningExpressionOfAnotherMediumIsRefused(): void
    {
        $this->expectException(ExpressionFromForeignMediumException::class);

        (new RecordingExpressionBuilder())->compile(new TestMediumSpecificCondition(new DateTimeImmutable()));
    }

    public function testConditionOutsideTheVocabularyIsRefused(): void
    {
        $this->expectException(ConditionNotSupportedException::class);

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

    private function createValueOfArity(string $arity): mixed
    {
        return match ($arity) {
            ExpressionValueArityEnum::NONE => null,
            ExpressionValueArityEnum::LIST => ['Hrnek'],
            ExpressionValueArityEnum::RANGE => ['A', 'Z'],
            default => 'Hrnek',
        };
    }
}

final class TestMediumSpecificCondition implements MediumSpecificConditionInterface
{
    public ?ExpressionBuilderInterface $receivedExpressionBuilder = null;

    public function __construct(
        private readonly object $expression,
    ) {
    }

    #[Override]
    public function compile(ExpressionBuilderInterface $expressionBuilder): object
    {
        $this->receivedExpressionBuilder = $expressionBuilder;

        return $this->expression;
    }
}

final class TestUnknownCondition implements ConditionInterface
{
}
