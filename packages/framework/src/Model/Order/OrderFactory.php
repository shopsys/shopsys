<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class OrderFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(
        OrderData $orderData,
        string $orderNumber,
        string $urlHash,
        ?CustomerUser $customerUser,
    ): Order {
        $entityClassName = $this->entityNameResolver->resolve(Order::class);

        return new $entityClassName($orderData, $orderNumber, $urlHash, $customerUser);
    }
}
