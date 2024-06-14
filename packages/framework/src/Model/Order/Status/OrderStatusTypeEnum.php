<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class OrderStatusTypeEnum extends AbstractEnum
{
    public const string TYPE_NEW = 'new';
    public const string TYPE_IN_PROGRESS = 'in_progress';
    public const string TYPE_DONE = 'done';
    public const string TYPE_CANCELED = 'cancelled';

    public const array ALL_TYPES = [
        self::TYPE_NEW,
        self::TYPE_IN_PROGRESS,
        self::TYPE_DONE,
        self::TYPE_CANCELED,
    ];
}
