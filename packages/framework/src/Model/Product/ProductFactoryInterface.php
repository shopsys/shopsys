<?php

namespace Shopsys\FrameworkBundle\Model\Product;

interface ProductFactoryInterface
{

    public function create(ProductData $data): Product;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $data
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $variants
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function createMainVariant(ProductData $data, array $variants): Product;
}
