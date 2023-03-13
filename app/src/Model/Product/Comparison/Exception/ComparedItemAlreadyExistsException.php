<?php

declare(strict_types=1);

namespace App\Model\Product\Comparison\Exception;

class ComparedItemAlreadyExistsException extends ComparisonException
{
    protected const CODE = 'compared-item-already-exists';
}
