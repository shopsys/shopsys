<?php

declare(strict_types=1);

namespace App\Model\Order\Item;

use App\Model\Order\Item\Exception\OrderItemRelatedException;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\Loggable;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableChild;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem as BaseOrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;

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
 * @method __construct(\App\Model\Order\Order $order, string $name, \Shopsys\FrameworkBundle\Model\Pricing\Price $price, string $vatPercent, int $quantity, string $type, string|null $unitName, string|null $catnum)
 */
#[LoggableChild(Loggable::STRATEGY_INCLUDE_ALL)]
class OrderItem extends BaseOrderItem
{
    /**
     * @var \App\Model\Order\Item\OrderItem|null
     * @ORM\OneToOne(targetEntity="App\Model\Order\Item\OrderItem")
     * @ORM\JoinColumn(name="related_order_item_id", referencedColumnName="id", nullable=true)
     */
    private $relatedOrderItem;

    /**
     * @param \App\Model\Order\Item\OrderItem|null $relatedOrderItem
     */
    public function setRelatedOrderItem(?self $relatedOrderItem): void
    {
        if ($this->type !== OrderItemTypeEnum::TYPE_PRODUCT) {
            throw new OrderItemRelatedException('This kind of relation is not supported.', 500);
        }

        $this->relatedOrderItem = $relatedOrderItem;
    }
}
