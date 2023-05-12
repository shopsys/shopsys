<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CurrencyFactory implements CurrencyFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData $data
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function create(CurrencyData $data): Currency
    {
        $classData = $this->entityNameResolver->resolve(Currency::class);

        return new $classData($data);
    }
}
