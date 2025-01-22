<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class PriceListCsvColumnsEnum extends AbstractEnum
{
    public const string PRODUCT_CATNUM = 'product_catnum';
    public const string PRICE = 'price';
}
