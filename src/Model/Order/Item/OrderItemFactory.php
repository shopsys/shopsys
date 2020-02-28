<?php

declare(strict_types=1);

namespace App\Model\Order\Item;

use App\Model\Order\Order;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem as BaseOrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory as BaseOrderItemFactory;
use Shopsys\FrameworkBundle\Model\Order\Order as BaseOrder;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;

/**
 * @method \App\Model\Order\Item\OrderItem createPayment(\App\Model\Order\Order $order, string $name, \Shopsys\FrameworkBundle\Model\Pricing\Price $price, string $vatPercent, int $quantity, \App\Model\Payment\Payment $payment)
 * @method \App\Model\Order\Item\OrderItem createTransport(\App\Model\Order\Order $order, string $name, \Shopsys\FrameworkBundle\Model\Pricing\Price $price, string $vatPercent, int $quantity, \App\Model\Transport\Transport $transport)
 */
class OrderItemFactory extends BaseOrderItemFactory
{
    /**
     * @param \App\Model\Order\Order $order
     * @param string $name
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @param string $vatPercent
     * @param int $quantity
     * @param string|null $unitName
     * @param string|null $catnum
     * @param \App\Model\Product\Product|null $product
     * @return \App\Model\Order\Item\OrderItem
     */
    public function createProduct(
        BaseOrder $order,
        string $name,
        Price $price,
        string $vatPercent,
        int $quantity,
        ?string $unitName,
        ?string $catnum,
        ?BaseProduct $product = null
    ): BaseOrderItem {
        throw new \BadMethodCallException('Use ' . self::class . '::createProductByOrderItemData() instead of this method');
    }

    /**
     * @param \App\Model\Order\Item\OrderItemData $orderItemData
     * @param \App\Model\Order\Order $order
     * @param \App\Model\Product\Product|null $product
     * @return \App\Model\Order\Item\OrderItem
     */
    public function createProductByOrderItemData(OrderItemData $orderItemData, Order $order, ?Product $product): OrderItem
    {
        /** @var \App\Model\Order\Item\OrderItem $orderItem */
        $orderItem = parent::createProduct(
            $order,
            $orderItemData->name,
            new Price($orderItemData->priceWithoutVat, $orderItemData->priceWithVat),
            $orderItemData->vatPercent,
            $orderItemData->quantity,
            $orderItemData->unitName,
            $orderItemData->catnum,
            $product
        );
        $orderItem->setProductType($orderItemData->productType);

        return $orderItem;
    }
}
