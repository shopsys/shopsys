<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ProductFactory implements ProductFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function create(ProductData $data): Product
    {
        $entityClassName = $this->entityNameResolver->resolve(Product::class);

        $product = $entityClassName::create($data);

        return $product;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $data
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainProduct
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $variants
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function createMainVariant(ProductData $data, Product $mainProduct, array $variants): Product
    {
        $variants[] = $mainProduct;

        $entityClassName = $this->entityNameResolver->resolve(Product::class);

        $mainVariant = $entityClassName::createMainVariant($data, $variants);

        return $mainVariant;
    }
}
