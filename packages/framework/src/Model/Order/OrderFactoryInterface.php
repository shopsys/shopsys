<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

interface OrderFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param string $orderNumber
     * @param string $urlHash
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function create(
        OrderData $orderData,
        string $orderNumber,
        string $urlHash,
        ?CustomerUser $customerUser,
    ): Order;
}
