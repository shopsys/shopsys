<?php

declare(strict_types=1);

namespace App\Model\Order\Item;

use App\Model\Order\Item\Exception\OrderItemRelatedException;
use App\Model\Product\Type\ProductType;
use App\Model\Stock\Stock;
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
     * @var \App\Model\Product\Type\ProductType
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Type\ProductType")
     * @ORM\JoinColumn(name="product_type_id", referencedColumnName="id", nullable=false)
     */
    private $productType;

    /**
     * @var \App\Model\Stock\Stock|null
     * @ORM\ManyToOne(targetEntity="App\Model\Stock\Stock")
     * @ORM\JoinColumn(name="personal_pickup_stock_id", referencedColumnName="id", nullable=true)
     */
    private $personalPickupStock;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $promoCodeIdentifier;

    /**
     * @var \App\Model\Order\Item\OrderItem|null
     * @ORM\OneToOne(targetEntity="App\Model\Order\Item\OrderItem")
     * @ORM\JoinColumn(name="related_order_item_id", referencedColumnName="id", nullable=true)
     */
    private $relatedOrderItem;

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

    /**
     * @return \App\Model\Product\Type\ProductType
     */
    public function getProductType(): ProductType
    {
        return $this->productType;
    }

    /**
     * @param \App\Model\Product\Type\ProductType $productType
     */
    public function setProductType(ProductType $productType): void
    {
        $this->productType = $productType;
    }

    /**
     * @return \App\Model\Stock\Stock|null
     */
    public function getPersonalPickupStock(): ?Stock
    {
        return $this->personalPickupStock;
    }

    /**
     * @param \App\Model\Stock\Stock|null $personalPickupStock
     */
    public function setPersonalPickupStock(?Stock $personalPickupStock): void
    {
        $this->personalPickupStock = $personalPickupStock;
    }

    /**
     * @return string|null
     */
    public function getPromoCodeIdentifier(): ?string
    {
        return $this->promoCodeIdentifier;
    }

    /**
     * @param string|null $promoCodeIdentifier
     */
    public function setPromoCodeIdentifier(?string $promoCodeIdentifier): void
    {
        $this->promoCodeIdentifier = $promoCodeIdentifier;
    }

    /**
     * @param \App\Model\Order\Item\OrderItem|null $relatedOrderItem
     */
    public function setRelatedOrderItem(?self $relatedOrderItem): void
    {
        if ($this->type !== self::TYPE_PRODUCT) {
            throw new OrderItemRelatedException('This kind of relation is not supported.', 500);
        }

        $this->relatedOrderItem = $relatedOrderItem;
    }

    /**
     * @return \App\Model\Order\Item\OrderItem|null
     */
    public function getRelatedCoupon(): ?self
    {
        if ($this->type !== self::TYPE_PRODUCT || $this->promoCodeIdentifier !== null) {
            throw new OrderItemRelatedException('This kind of relation is not supported.', 500);
        }

        return $this->relatedOrderItem;
    }

    /**
     * @return \App\Model\Order\Item\OrderItem|null
     */
    public function getRelatedProduct(): ?self
    {
        if ($this->type !== self::TYPE_PRODUCT || $this->promoCodeIdentifier === null) {
            throw new OrderItemRelatedException('This kind of relation is not supported.', 500);
        }

        return $this->relatedOrderItem;
    }

    /**
     * @return string
     */
    public function getDiscountText(): string
    {
        return str_replace(' - ' . $this->getRelatedProduct()->getName(), '', $this->name);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
