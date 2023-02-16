<?php

declare(strict_types=1);

namespace App\Model\Product\Availability;

enum AvailabilityStatusEnum:string
{
    case InStock = 'in-stock';
    case OutOfStock = 'out-of-stock';
}
