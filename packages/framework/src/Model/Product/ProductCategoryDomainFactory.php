<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Category\Category;

class ProductCategoryDomainFactory implements ProductCategoryDomainFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain
     */
    public function create(
        Product $product,
        Category $category,
        int $domainId,
    ): ProductCategoryDomain {
        $classData = $this->entityNameResolver->resolve(ProductCategoryDomain::class);

        return new $classData($product, $category, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[][] $categoriesIndexedByDomainId
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain[]
     */
    public function createMultiple(
        Product $product,
        array $categoriesIndexedByDomainId,
    ): array {
        $productCategoryDomains = [];
        foreach ($categoriesIndexedByDomainId as $domainId => $categoriesOnDomain) {
            foreach ($categoriesOnDomain as $category) {
                $productCategoryDomains[] = $this->create(
                    $product,
                    $category,
                    $domainId,
                );
            }
        }

        return $productCategoryDomains;
    }
}
