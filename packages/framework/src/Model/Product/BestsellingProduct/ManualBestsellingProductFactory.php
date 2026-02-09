<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\BestsellingProduct;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ManualBestsellingProductFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(
        int $domainId,
        Category $category,
        Product $product,
        int $position,
    ): ManualBestsellingProduct {
        $entityClassName = $this->entityNameResolver->resolve(ManualBestsellingProduct::class);

        return new $entityClassName($domainId, $category, $product, $position);
    }
}
