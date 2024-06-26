<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class OrderFactory
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
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function create(
        OrderData $orderData,
        string $orderNumber,
        string $urlHash,
    ): Order {
        $entityClassName = $this->entityNameResolver->resolve(Order::class);

        return new $entityClassName($orderData, $orderNumber, $urlHash);
    }
}
