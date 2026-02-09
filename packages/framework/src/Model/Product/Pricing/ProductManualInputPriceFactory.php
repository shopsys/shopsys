<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductManualInputPriceFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(
        Product $product,
        PricingGroup $pricingGroup,
        ?Money $inputPrice,
    ): ProductManualInputPrice {
        $entityClassName = $this->entityNameResolver->resolve(ProductManualInputPrice::class);

        return new $entityClassName($product, $pricingGroup, $inputPrice);
    }
}
