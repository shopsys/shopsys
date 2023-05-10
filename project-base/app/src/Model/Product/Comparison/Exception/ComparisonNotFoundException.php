<?php

declare(strict_types=1);

namespace App\Model\Product\Comparison\Exception;

class ComparisonNotFoundException extends ComparisonException
{
    protected const CODE = 'comparison-not-found';
}
