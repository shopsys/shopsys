<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Money\Money;

class TransportPriceFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param int $domainId
     * @param int|null $maxWeight
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportPrice
     */
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
