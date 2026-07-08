<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class ProductPricesMulticurrencyModeEnum extends AbstractEnum
{
    public const string MODE_CALCULATED = 'calculated';
    public const string MODE_MANUAL = 'manual';
}
