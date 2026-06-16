<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\ReturnHash;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Order\Order;

class PaymentReturnHashFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(string $hash, Order $order, DateTimeImmutable $expiresAt): PaymentReturnHash
    {
        $entityClassName = $this->entityNameResolver->resolve(PaymentReturnHash::class);

        return new $entityClassName($hash, $order, $expiresAt);
    }
}
