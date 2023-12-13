<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\ExpressionLanguage;

use Overblog\GraphQLBundle\Definition\Argument;

/**
 * @see \Shopsys\FrontendApiBundle\Component\ExpressionLanguage\DynamicPaginationComplexityExpressionFunction
 */
class ComplexityCalculator
{
    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param int $oneItemComplexity
     * @param int $defaultCount
     * @return int
     */
    public static function calculate(Argument $argument, int $oneItemComplexity, int $defaultCount): int
    {
        if ($argument->offsetExists('first')) {
            return $argument['first'] * $oneItemComplexity;
        }

        if ($argument->offsetExists('last')) {
            return $argument['last'] * $oneItemComplexity;
        }

        return $defaultCount * $oneItemComplexity;
    }
}
