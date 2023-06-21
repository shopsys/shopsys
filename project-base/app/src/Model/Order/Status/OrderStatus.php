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
        ], true)) {
            throw new InvalidOrderStatusTypeException($type);
        }

        $this->type = $type;
    }
}
