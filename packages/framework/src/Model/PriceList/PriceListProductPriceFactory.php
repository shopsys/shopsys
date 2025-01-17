<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class PriceListProductPriceFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPriceData $priceListProductPriceData
     * @return \Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPrice
     */
    public function create(
        PriceListProductPriceData $priceListProductPriceData,
    ): PriceListProductPrice {
        $entityClassName = $this->entityNameResolver->resolve(PriceListProductPrice::class);

        return new $entityClassName($priceListProductPriceData);
    }
}
