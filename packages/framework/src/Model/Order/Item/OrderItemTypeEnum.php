<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class OrderItemTypeEnum extends AbstractEnum
{
    public const string TYPE_PAYMENT = 'payment';
    public const string TYPE_PRODUCT = 'product';
    public const string TYPE_DISCOUNT = 'discount';
    public const string TYPE_TRANSPORT = 'transport';
    public const string TYPE_ROUNDING = 'rounding';

    protected const array SORTED_TYPES = [
        self::TYPE_PRODUCT,
        self::TYPE_PAYMENT,
        self::TYPE_TRANSPORT,
        self::TYPE_DISCOUNT,
        self::TYPE_ROUNDING,
    ];

    /**
     * @return string[]
     */
    public function getAllCasesSortedByPriority(): array
    {
        return array_unique(array_merge(static::SORTED_TYPES, $this->getAllCases()));
    }
}
