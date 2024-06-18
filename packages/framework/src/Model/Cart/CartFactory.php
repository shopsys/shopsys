<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;

class CartFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderDataFactory $orderDataFactory
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly OrderDataFactory $orderDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function create(CustomerUserIdentifier $customerUserIdentifier): Order
    {
        $orderData = $this->orderDataFactory->createCartOrderData($customerUserIdentifier);
        $entityClassName = $this->entityNameResolver->resolve(Order::class);

        return new $entityClassName($orderData);
    }
}
