<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class OrderStatusFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly OrderStatusTypeEnum $orderStatusTypeEnum,
    ) {
    }

    public function create(OrderStatusData $data, string $type, string $code): OrderStatus
    {
        $this->orderStatusTypeEnum->validateCase($type);
        $entityClassName = $this->entityNameResolver->resolve(OrderStatus::class);

        return new $entityClassName($data, $type, $code);
    }
}
