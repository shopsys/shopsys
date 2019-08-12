<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

interface ProductChangeMessageProducerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    public function productChanged(Product $product): void;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    public function productsChanged(array $products): void;

    /**
     * @param int $productId
     */
    public function productChangedById(int $productId): void;

    /**
     * @param int[] $productIds
     */
    public function productsChangedByIds(array $productIds): void;
}
