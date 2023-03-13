<?php

declare(strict_types=1);

namespace App\Model\Product\Comparison\Exception;

class ComparedItemNotFoundException extends ComparisonException
{
    protected const CODE = 'compared-item-not-found';
}
