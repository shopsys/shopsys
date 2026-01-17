<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class ProductVisibilityFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(
        Product $product,
        PricingGroup $pricingGroup,
        int $domainId,
    ): ProductVisibility {
        $entityClassName = $this->entityNameResolver->resolve(ProductVisibility::class);

        return new $entityClassName($product, $pricingGroup, $domainId);
    }
}
