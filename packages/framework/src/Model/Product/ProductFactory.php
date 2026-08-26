<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ProductFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(ProductData $data): Product
    {
        $entityClassName = $this->entityNameResolver->resolve(Product::class);

        return $entityClassName::create($data);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $variants
     */
    public function createMainVariant(ProductData $data, Product $mainProduct, array $variants): Product
    {
        $variants[] = $mainProduct;

        $entityClassName = $this->entityNameResolver->resolve(Product::class);

        return $entityClassName::createMainVariant($data, $variants);
    }
}
