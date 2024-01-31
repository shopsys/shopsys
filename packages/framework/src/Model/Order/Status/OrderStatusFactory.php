<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class OrderStatusFactory implements OrderStatusFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData $data
     * @param int $type
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
     */
    public function create(OrderStatusData $data, int $type): OrderStatus
    {
        $entityClassName = $this->entityNameResolver->resolve(OrderStatus::class);

        return new $entityClassName($data, $type);
    }
}
