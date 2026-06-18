<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\ExpressionLanguage;

use Overblog\GraphQLBundle\Definition\Argument;

/**
 * @see \Shopsys\FrontendApiBundle\Component\ExpressionLanguage\DynamicPaginationComplexityExpressionFunction
 */
class ComplexityCalculator
{
    public static function calculate(
        Argument $argument,
        int $oneItemComplexity,
        int $defaultCount,
        int $childrenComplexity = 0,
    ): int {
        $itemComplexity = $childrenComplexity + $oneItemComplexity;

        if ($argument->offsetExists('first')) {
            return $argument['first'] * $itemComplexity;
        }

        if ($argument->offsetExists('last')) {
            return $argument['last'] * $itemComplexity;
        }

        return $defaultCount * $itemComplexity;
    }
}
