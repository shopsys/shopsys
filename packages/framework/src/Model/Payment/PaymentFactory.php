<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class PaymentFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(PaymentData $data): Payment
    {
        $entityClassName = $this->entityNameResolver->resolve(Payment::class);

        return new $entityClassName($data);
    }
}
