<?php

declare(strict_types=1);

namespace App\Model\Order\Status;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\Status\Exception\InvalidOrderStatusTypeException;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus as BaseOrderStatus;

/**
 * @ORM\Table(name="order_statuses")
 * @ORM\Entity
 */
class OrderStatus extends BaseOrderStatus
{
    public const TYPE_IM_SENT = 6;
    public const TYPE_IM_ERROR = 7;
    public const TYPE_ERP_INSTOCK = 8;
    public const TYPE_ERP_INTRANSIT = 9;
    public const TYPE_ERP_WAITING = 10;
    public const TYPE_ERP_ORDERED = 11;
    public const TYPE_ERP_ERROR = 12;

    /**
     * @param int $type
     */
    protected function setType($type)
    {
        if (!in_array($type, [
            self::TYPE_NEW,
            self::TYPE_IN_PROGRESS,
            self::TYPE_DONE,
            self::TYPE_CANCELED,
            self::TYPE_IM_SENT,
            self::TYPE_IM_ERROR,
            self::TYPE_ERP_INSTOCK,
            self::TYPE_ERP_INTRANSIT,
            self::TYPE_ERP_WAITING,
            self::TYPE_ERP_ORDERED,
            self::TYPE_ERP_ERROR,
        ], true)) {
            throw new InvalidOrderStatusTypeException($type);
        }

        $this->type = $type;
    }
}
