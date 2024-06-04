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
}
