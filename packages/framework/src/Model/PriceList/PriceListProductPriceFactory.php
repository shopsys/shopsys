<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class PriceListProductPriceFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(
        PriceListProductPriceData $priceListProductPriceData,
    ): PriceListProductPrice {
        $entityClassName = $this->entityNameResolver->resolve(PriceListProductPrice::class);

        return new $entityClassName($priceListProductPriceData);
    }
}
