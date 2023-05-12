<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Money\Money;

class TransportPriceFactory implements TransportPriceFactoryInterface
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
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportPrice
     */
    public function create(
        Transport $transport,
        Money $price,
        int $domainId
    ): TransportPrice {
        $classData = $this->entityNameResolver->resolve(TransportPrice::class);

        return new $classData($transport, $price, $domainId);
    }
}
