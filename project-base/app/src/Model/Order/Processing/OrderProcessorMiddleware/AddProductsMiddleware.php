<?php

declare(strict_types=1);

namespace App\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddProductsMiddleware as BaseAddProductsMiddleware;

class AddProductsMiddleware extends BaseAddProductsMiddleware
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice $quantifiedItemPrice
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param string $locale
     * @return \App\Model\Order\Item\OrderItemData
     */
    public function createProductOrderItem(
        QuantifiedItemPrice $quantifiedItemPrice,
        QuantifiedProduct $quantifiedProduct,
        string $locale,
    ): OrderItemData {
        /** @var \App\Model\Order\Item\OrderItemData $orderItemData */
        $orderItemData = parent::createProductOrderItem($quantifiedItemPrice, $quantifiedProduct, $locale);

        /** @var \App\Model\Product\Product $product */
        $product = $quantifiedProduct->getProduct();

        $orderItemData->name = $product->getFullname($locale);

        return $orderItemData;
    }
}
