<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Autocomplete;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Product;

class AutocompleteFavoriteProductFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(Product $product, int $domainId, int $position): AutocompleteFavoriteProduct
    {
        $className = $this->entityNameResolver->resolve(AutocompleteFavoriteProduct::class);

        return new $className($product, $domainId, $position);
    }
}
