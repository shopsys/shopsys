<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Order\Order;

class WithdrawalRequestFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(Order $order, WithdrawalRequestData $withdrawalRequestData): WithdrawalRequest
    {
        $entityClassName = $this->entityNameResolver->resolve(WithdrawalRequest::class);

        return new $entityClassName($order, $withdrawalRequestData);
    }
}
