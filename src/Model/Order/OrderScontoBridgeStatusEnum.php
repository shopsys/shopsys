<?php

declare(strict_types=1);

namespace App\Model\Order;

use Enum\AbstractEnum;

class OrderScontoBridgeStatusEnum extends AbstractEnum
{
    public const STATUS_NEW = 'new';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_DONE = 'done';
    public const STATUS_ERROR = 'error';
}
