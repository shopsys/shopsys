<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

enum AvailabilityStatusEnum: string implements AvailabilityStatusEnumInterface
{
    case InStock = 'in-stock';
    case OutOfStock = 'out-of-stock';
}
