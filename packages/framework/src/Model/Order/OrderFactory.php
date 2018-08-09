<?php

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Customer\User;

class OrderFactory implements OrderFactoryInterface
{

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User|null $user
     */
    public function create(
        OrderData $orderData,
        string $orderNumber,
        string $urlHash,
        ?User $user
    ): Order {
        return new Order($orderData, $orderNumber, $urlHash, $user);
    }
}
