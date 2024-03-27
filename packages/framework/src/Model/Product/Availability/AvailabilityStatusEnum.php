<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnumCasesProvider;

class AvailabilityStatusEnum extends AbstractEnumCasesProvider
{
    public const string IN_STOCK = 'InStock';
    public const string OUT_OF_STOCK = 'OutOfStock';
}
