<?php

declare(strict_types=1);

namespace App\Model\Order\Item;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\Loggable;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableChild;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem as BaseOrderItem;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @property \App\Model\Order\Order $order
 * @property \App\Model\Transport\Transport|null $transport
 * @property \App\Model\Payment\Payment|null $payment
 * @property \App\Model\Product\Product|null $product
 * @method \App\Model\Order\Order getOrder()
 * @method \App\Model\Transport\Transport getTransport()
 * @method \App\Model\Payment\Payment getPayment()
 * @method \App\Model\Product\Product|null getProduct()
 * @method void edit(\App\Model\Order\Item\OrderItemData $orderItemData)
 * @method void setTransport(\App\Model\Transport\Transport $transport)
 * @method void setPayment(\App\Model\Payment\Payment $payment)
 * @method void setProduct(\App\Model\Product\Product|null $product)
 * @method __construct(\App\Model\Order\Order $order, string $name, \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $price, string $vatPercent, int $quantity, string $type, string|null $unitName, string|null $catnum)
 * @property \Doctrine\Common\Collections\Collection<int, \App\Model\Order\Item\OrderItem> $relatedItems
 * @method addRelatedItem(\App\Model\Order\Item\OrderItem $relatedItem)
 * @method \App\Model\Order\Item\OrderItem[] getRelatedItems()
 * @method void setProductGift(\App\Model\Product\Product|null $productGift)
 * @method \App\Model\Product\Product|null getProductGift()
 */
#[AsMcpTable]
#[LoggableChild(Loggable::STRATEGY_INCLUDE_ALL)]
#[ORM\Table(name: 'order_items')]
#[ORM\Entity]
class OrderItem extends BaseOrderItem
{
}
