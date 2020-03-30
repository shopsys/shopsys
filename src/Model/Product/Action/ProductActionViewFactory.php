<?php

declare(strict_types=1);

namespace App\Model\Product\Action;

use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\ReadModelBundle\Product\Action\ProductActionView as BaseProductActionView;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory as BaseProductActionViewFactory;

/**
 * @experimental
 */
class ProductActionViewFactory extends BaseProductActionViewFactory
{
    /**
     * @param \App\Model\Product\Product $product
     * @param string $absoluteUrl
     * @return \App\Model\Product\Action\ProductActionView
     */
    public function createFromProduct(BaseProduct $product, string $absoluteUrl): BaseProductActionView
    {
        return new ProductActionView(
            $product->getId(),
            $product->isSellingDenied(),
            $product->isMainVariant(),
            $absoluteUrl,
            null
        );
    }

    /**
     * @param array $productArray
     * @return \App\Model\Product\Action\ProductActionView
     */
    public function createFromArray(array $productArray): BaseProductActionView
    {
        return new ProductActionView(
            $productArray['id'],
            $productArray['selling_denied'],
            $productArray['is_main_variant'],
            $productArray['detail_url'],
            $productArray['stock_quantity']
        );
    }
}
