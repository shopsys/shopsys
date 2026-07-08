<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\TransportAndPayment;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class FreeTransportAndPaymentPriceLimitFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(int $domainId, Currency $currency, Money $price): FreeTransportAndPaymentPriceLimit
    {
        $entityClassName = $this->entityNameResolver->resolve(FreeTransportAndPaymentPriceLimit::class);

        return new $entityClassName($domainId, $currency, $price);
    }
}
