<?php

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Customer\User;

class OrderFactory implements OrderFactoryInterface
{
    public function create(
        OrderData $orderData,
        string $orderNumber,
        string $urlHash,
        ?User $user
    ): Order {
        return new Order($orderData, $orderNumber, $urlHash, $user);
    }
}
