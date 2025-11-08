<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Order\Order;

class WithdrawalRequestFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestData $withdrawalRequestData
     * @return \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest
     */
    public function create(Order $order, WithdrawalRequestData $withdrawalRequestData): WithdrawalRequest
    {
        $entityClassName = $this->entityNameResolver->resolve(WithdrawalRequest::class);

        return new $entityClassName($order, $withdrawalRequestData);
    }
}
