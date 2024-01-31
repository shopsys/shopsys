<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\BestsellingProduct;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ManualBestsellingProductFactory implements ManualBestsellingProductFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $position
     * @return \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\ManualBestsellingProduct
     */
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
