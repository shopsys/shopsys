<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnumCasesProvider;

class ProductListTypeEnum extends AbstractEnumCasesProvider
{
    public const string WISHLIST = 'wishlist';
    public const string COMPARISON = 'comparison';
}
