<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class PaymentPriceFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(
        Payment $payment,
        Money $price,
        int $domainId,
        Currency $currency,
    ): PaymentPrice {
        $entityClassName = $this->entityNameResolver->resolve(PaymentPrice::class);

        return new $entityClassName($payment, $price, $domainId, $currency);
    }
}
