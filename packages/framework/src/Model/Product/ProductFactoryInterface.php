<?php

namespace Shopsys\FrameworkBundle\Model\Product;

interface ProductFactoryInterface
{
    public function create(ProductData $data): Product;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $variants
     */
    public function createMainVariant(ProductData $data, array $variants): Product;
}
