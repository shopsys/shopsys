<?php

declare(strict_types=1);

namespace App\Model\Order\Status;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus as BaseOrderStatus;

/**
 * @ORM\Table(name="order_statuses")
 * @ORM\Entity
 */
class OrderStatus extends BaseOrderStatus
{
    public const TYPE_OVER_LIMIT = 5;

    /**
     * @param int $type
     */
    protected function setType($type)
    {
        if (in_array($type, [
            self::TYPE_NEW,
            self::TYPE_IN_PROGRESS,
            self::TYPE_DONE,
            self::TYPE_CANCELED,
            self::TYPE_OVER_LIMIT,
        ], true)) {
            $this->type = $type;
        } else {
            throw new \Shopsys\FrameworkBundle\Model\Order\Status\Exception\InvalidOrderStatusTypeException($type);
        }
    }
}
