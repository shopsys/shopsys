<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Item;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;

class CartItemTypeEnum extends AbstractEnum
{
    public const string TYPE_PRODUCT = OrderItemTypeEnum::TYPE_PRODUCT;
    public const string TYPE_PRODUCT_GIFT = OrderItemTypeEnum::TYPE_PRODUCT_GIFT;
}
