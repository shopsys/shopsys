<?php

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class OrderFactory implements OrderFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

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
        ?CustomerUser $customerUser
    ): Order {
        $classData = $this->entityNameResolver->resolve(Order::class);

        return new $classData($orderData, $orderNumber, $urlHash, $customerUser);
    }
}
