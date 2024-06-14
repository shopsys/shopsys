<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class OrderStatusFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum $orderStatusTypeEnum
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly OrderStatusTypeEnum $orderStatusTypeEnum,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData $data
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
     */
    public function create(OrderStatusData $data, string $type): OrderStatus
    {
        $this->orderStatusTypeEnum->validateCase($type);
        $entityClassName = $this->entityNameResolver->resolve(OrderStatus::class);

        return new $entityClassName($data, $type);
    }
}
