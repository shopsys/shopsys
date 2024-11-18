<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ProductWithPriceFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPriceData $productWithPriceData
     * @return \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPrice
     */
    public function create(
        ProductWithPriceData $productWithPriceData,
    ): ProductWithPrice {
        $entityClassName = $this->entityNameResolver->resolve(ProductWithPrice::class);

        return new $entityClassName($productWithPriceData);
    }
}
