<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Money\Money;

class PaymentPriceFactory implements PaymentPriceFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentPrice
     */
    public function create(
        Payment $payment,
        Money $price,
        int $domainId,
    ): PaymentPrice {
        $entityClassName = $this->entityNameResolver->resolve(PaymentPrice::class);

        return new $entityClassName($payment, $price, $domainId);
    }
}
