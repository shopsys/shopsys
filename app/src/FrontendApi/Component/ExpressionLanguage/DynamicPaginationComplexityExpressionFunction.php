<?php

declare(strict_types=1);

namespace App\FrontendApi\Component\ExpressionLanguage;

use Overblog\GraphQLBundle\ExpressionLanguage\ExpressionFunction as BaseExpressionFunction;

class DynamicPaginationComplexityExpressionFunction extends BaseExpressionFunction
{
    public function __construct()
    {
        parent::__construct('dynamicPaginationComplexity', function (string $args = '[]', string $oneItemComplexity = '1', string $defaultCount = '10') {
            return "\App\FrontendApi\Component\ExpressionLanguage\ComplexityCalculator::calculate(${args}, ${oneItemComplexity}, ${defaultCount})";
        });
    }
}
