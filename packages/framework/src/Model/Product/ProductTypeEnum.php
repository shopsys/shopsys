<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class ProductTypeEnum extends AbstractEnum
{
    public const string TYPE_BASIC = 'basic';

    public const string TYPE_INQUIRY = 'inquiry';
}
