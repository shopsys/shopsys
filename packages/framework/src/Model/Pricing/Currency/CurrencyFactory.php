<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CurrencyFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(CurrencyData $data): Currency
    {
        $entityClassName = $this->entityNameResolver->resolve(Currency::class);

        return new $entityClassName($data);
    }
}
