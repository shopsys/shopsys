<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\ExpressionLanguage;

use Overblog\GraphQLBundle\ExpressionLanguage\ExpressionFunction;

class DynamicPaginationComplexityExpressionFunction extends ExpressionFunction
{
    public function __construct()
    {
        parent::__construct('dynamicPaginationComplexity', function (string $args = '[]', string $oneItemComplexity = '1', string $defaultCount = '10') {
            return '\\' . ComplexityCalculator::class . "::calculate(${args}, ${oneItemComplexity}, ${defaultCount})";
        });
    }
}
