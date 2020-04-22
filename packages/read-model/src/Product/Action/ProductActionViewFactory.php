<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Action;

use Shopsys\FrameworkBundle\Model\Product\Product;

/**
 * @experimental
 */
class ProductActionViewFactory
{
    /**
     * @param int $id
     * @param bool $sellingDenied
     * @param bool $isMainVariant
     * @param string $detailUrl
     * @return \Shopsys\ReadModelBundle\Product\Action\ProductActionView
     */
    protected function create(int $id, bool $sellingDenied, bool $isMainVariant, string $detailUrl): ProductActionView
    {
        return new ProductActionView(
            $id,
            $sellingDenied,
            $isMainVariant,
            $detailUrl
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param string $absoluteUrl
     * @return \Shopsys\ReadModelBundle\Product\Action\ProductActionView
     */
    public function createFromProduct(Product $product, string $absoluteUrl): ProductActionView
    {
        return $this->create(
            $product->getId(),
            $product->isSellingDenied(),
            $product->isMainVariant(),
            $absoluteUrl
        );
    }

    /**
     * @param array $productArray
     * @return \Shopsys\ReadModelBundle\Product\Action\ProductActionView
     */
    public function createFromArray(array $productArray): ProductActionView
    {
        return $this->create(
            $productArray['id'],
            $productArray['selling_denied'],
            $productArray['is_main_variant'],
            $productArray['detail_url']
        );
    }
}
