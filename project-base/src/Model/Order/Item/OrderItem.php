<?php

declare(strict_types=1);

namespace App\Model\Order\Item;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem as BaseOrderItem;
use Shopsys\FrameworkBundle\Model\Order\Order as BaseOrder;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

/**
 * @ORM\Table(name="order_items")
 * @ORM\Entity
 * @property \App\Model\Order\Order $order
 * @property \App\Model\Transport\Transport|null $transport
 * @property \App\Model\Payment\Payment|null $payment
 * @property \App\Model\Product\Product|null $product
 * @method \App\Model\Order\Order getOrder()
 * @method \App\Model\Transport\Transport getTransport()
 * @method \App\Model\Payment\Payment getPayment()
 * @method \App\Model\Product\Product|null getProduct()
 * @method edit(\App\Model\Order\Item\OrderItemData $orderItemData)
 * @method setTransport(\App\Model\Transport\Transport $transport)
 * @method setPayment(\App\Model\Payment\Payment $payment)
 * @method setProduct(\App\Model\Product\Product|null $product)
 */
class OrderItem extends BaseOrderItem
{
    /**
     * @param \App\Model\Order\Order $order
     * @param string $name
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @param string $vatPercent
     * @param int $quantity
     * @param string $type
     * @param null|string $unitName
     * @param null|string $catnum
     */
    public function __construct(
        BaseOrder $order,
        string $name,
        Price $price,
        string $vatPercent,
        int $quantity,
        string $type,
        ?string $unitName,
        ?string $catnum
    ) {
        parent::__construct(
            $order,
            $name,
            $price,
            $vatPercent,
            $quantity,
            $type,
            $unitName,
            $catnum
        );
    }
}
