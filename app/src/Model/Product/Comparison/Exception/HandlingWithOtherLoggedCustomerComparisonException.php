<?php

declare(strict_types=1);

namespace App\Model\Product\Comparison\Exception;

class HandlingWithOtherLoggedCustomerComparisonException extends ComparisonException
{
    protected const CODE = 'handling-with-logged-customer-comparison';
}
