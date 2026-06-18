<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Component\ExpressionLanguage;

use Overblog\GraphQLBundle\Definition\Argument;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrontendApiBundle\Component\ExpressionLanguage\ComplexityCalculator;
use Shopsys\FrontendApiBundle\Component\ExpressionLanguage\DynamicPaginationComplexityExpressionFunction;

final class ComplexityCalculatorTest extends TestCase
{
    /**
     * @param array<string, int> $arguments
     */
    #[DataProvider('calculateDataProvider')]
    public function testCalculateIncludesChildrenComplexity(
        array $arguments,
        int $oneItemComplexity,
        int $defaultCount,
        int $childrenComplexity,
        int $expectedComplexity,
    ): void {
        $argument = new Argument($arguments);

        $actualComplexity = ComplexityCalculator::calculate(
            $argument,
            $oneItemComplexity,
            $defaultCount,
            $childrenComplexity,
        );

        $this->assertSame($expectedComplexity, $actualComplexity);
    }

    /**
     * @return iterable<string, array{
     *     arguments: array<string, int>,
     *     oneItemComplexity: int,
     *     defaultCount: int,
     *     childrenComplexity: int,
     *     expectedComplexity: int,
     * }>
     */
    public static function calculateDataProvider(): iterable
    {
        yield 'first argument multiplies item and children complexity' => [
            'arguments' => [
                'first' => 3,
            ],
            'oneItemComplexity' => 2,
            'defaultCount' => 10,
            'childrenComplexity' => 5,
            'expectedComplexity' => 21,
        ];

        yield 'last argument multiplies item and children complexity' => [
            'arguments' => [
                'last' => 4,
            ],
            'oneItemComplexity' => 3,
            'defaultCount' => 10,
            'childrenComplexity' => 7,
            'expectedComplexity' => 40,
        ];

        yield 'default count multiplies item and children complexity' => [
            'arguments' => [],
            'oneItemComplexity' => 1,
            'defaultCount' => 10,
            'childrenComplexity' => 8,
            'expectedComplexity' => 90,
        ];

        yield 'zero children complexity keeps original item calculation' => [
            'arguments' => ['first' => 3],
            'oneItemComplexity' => 2,
            'defaultCount' => 10,
            'childrenComplexity' => 0,
            'expectedComplexity' => 6,
        ];
    }

    public function testDynamicPaginationComplexityCompilerPassesChildrenComplexity(): void
    {
        $expressionFunction = new DynamicPaginationComplexityExpressionFunction();
        $expectedCompiledExpression = sprintf(
            '\%s::calculate(args, 1, 10, $childrenComplexity)',
            ComplexityCalculator::class,
        );

        $compiledExpression = ($expressionFunction->getCompiler())('args');

        $this->assertSame($expectedCompiledExpression, $compiledExpression);
    }
}
