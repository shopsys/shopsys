<?php

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Customer\User;

interface OrderFactoryInterface
{

    public function create(
        OrderData $orderData,
        string $orderNumber,
        string $urlHash,
        ?User $user
    ): Order;
}
