<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class ProductListTypeEnum extends AbstractEnum
{
    public const string WISHLIST = 'WISHLIST';
    public const string COMPARISON = 'COMPARISON';
}
