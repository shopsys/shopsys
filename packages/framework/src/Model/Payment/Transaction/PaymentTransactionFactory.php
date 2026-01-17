<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class PaymentTransactionFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(PaymentTransactionData $paymentTransactionData): PaymentTransaction
    {
        $className = $this->entityNameResolver->resolve(PaymentTransaction::class);

        return new $className($paymentTransactionData);
    }
}
