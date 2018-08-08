<?php

namespace Shopsys\FrameworkBundle\Model\Product;

interface ProductDataFactoryInterface
{
    public function create(): ProductData;

    public function createFromProduct(Product $product): ProductData;
}
