<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Money\Money;

class TransportPriceFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(
        Transport $transport,
        Money $price,
        int $domainId,
        ?int $maxWeight,
    ): TransportPrice {
        $entityClassName = $this->entityNameResolver->resolve(TransportPrice::class);

        return new $entityClassName($transport, $price, $domainId, $maxWeight);
    }
}
