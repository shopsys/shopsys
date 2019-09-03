<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\Model\Order\Item;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem as BaseOrderItem;
use Shopsys\FrameworkBundle\Model\Order\Order as BaseOrder;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

/**
 * @ORM\Table(name="order_items")
 * @ORM\Entity
 * @property \Shopsys\ShopBundle\Model\Order\Order $order
 * @property \Shopsys\ShopBundle\Model\Transport\Transport|null $transport
 * @property \Shopsys\ShopBundle\Model\Payment\Payment|null $payment
 * @property \Shopsys\ShopBundle\Model\Product\Product|null $product
 * @method \Shopsys\ShopBundle\Model\Order\Order getOrder()
 * @method \Shopsys\ShopBundle\Model\Transport\Transport getTransport()
 * @method \Shopsys\ShopBundle\Model\Payment\Payment getPayment()
 * @method \Shopsys\ShopBundle\Model\Product\Product|null getProduct()
 * @method edit(\Shopsys\ShopBundle\Model\Order\Item\OrderItemData $orderItemData)
 * @method setTransport(\Shopsys\ShopBundle\Model\Transport\Transport $transport)
 * @method setPayment(\Shopsys\ShopBundle\Model\Payment\Payment $payment)
 * @method setProduct(\Shopsys\ShopBundle\Model\Product\Product|null $product)
 */
class OrderItem extends BaseOrderItem
{
    /**
     * @param \Shopsys\ShopBundle\Model\Order\Order $order
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
