<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class OrderItemTypeEnum extends AbstractEnum
{
    public const string TYPE_PAYMENT = 'payment';
    public const string TYPE_PRODUCT = 'product';
    public const string TYPE_DISCOUNT = 'discount'; // TODO should we emphasise this one is just for the promo codes?
    public const string TYPE_TRANSPORT = 'transport';
    public const string TYPE_ROUNDING = 'rounding';
    public const string TYPE_PRODUCT_GIFT = 'productGift';
    public const string TYPE_PROMOTION = 'promotion';
    public const string TYPE_PRICE_LIST_DISCOUNT = 'priceListDiscount'; // TODO better name? SPECIAL_PRICE_DISCOUNT?
}
