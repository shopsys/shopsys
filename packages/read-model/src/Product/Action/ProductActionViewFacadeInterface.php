<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Action;

use Shopsys\FrameworkBundle\Model\Product\Product;

interface ProductActionViewFacadeInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return \Shopsys\ReadModelBundle\Product\Action\ProductActionView[]
     */
    public function getForProducts(array $products): array;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\ReadModelBundle\Product\Action\ProductActionView
     */
    public function getForProduct(Product $product): ProductActionView;
}
