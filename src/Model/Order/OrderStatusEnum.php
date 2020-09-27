<?php

declare(strict_types=1);

namespace App\Model\Order;

use Enum\AbstractEnum;

class OrderStatusEnum extends AbstractEnum
{
    public const STATUS_OVERLIMIT = 5;
    public const STATUS_IM_SENT = 6;
    public const STATUS_IM_ERROR = 7;
    public const STATUS_ERP_INSTOCK = 8;
    public const STATUS_ERP_INTRANSIT = 9;
    public const STATUS_ERP_WAITING = 10;
    public const STATUS_ERP_ORDERED = 11;
    public const STATUS_ERP_ERROR = 12;
}
