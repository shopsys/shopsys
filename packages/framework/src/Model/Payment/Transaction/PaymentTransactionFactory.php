<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class PaymentTransactionFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     * @return \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction
     */
    public function create(PaymentTransactionData $paymentTransactionData): PaymentTransaction
    {
        $className = $this->entityNameResolver->resolve(PaymentTransaction::class);

        return new $className($paymentTransactionData);
    }
}
