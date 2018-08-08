<?php

namespace Shopsys\FrameworkBundle\Model\Product;

class ProductFactory implements ProductFactoryInterface
{
    public function create(ProductData $data): Product
    {
        return Product::create($data);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $variants
     */
    public function createMainVariant(ProductData $data, array $variants): Product
    {
        return Product::createMainVariant($data, $variants);
    }
}
