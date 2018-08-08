<?php

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

/**
 * @ORM\Table(name="order_items")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "payment" = "\Shopsys\FrameworkBundle\Model\Order\Item\OrderPayment",
 *     "product" = "\Shopsys\FrameworkBundle\Model\Order\Item\OrderProduct",
 *     "transport" = "\Shopsys\FrameworkBundle\Model\Order\Item\OrderTransport"
 * })
 */
abstract class OrderItem
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Order
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Order\Order", inversedBy="items")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false)
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=20, scale=6)
     */
    protected $priceWithoutVat;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=20, scale=6)
     */
    protected $priceWithVat;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=20, scale=6)
     */
    protected $vatPercent;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $quantity;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $unitName;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $catnum;

    /**
     * @param string|null $unitName
     * @param string|null $catnum
     */
    public function __construct(
        Order $order,
        string $name,
        Price $price,
        string $vatPercent,
        int $quantity,
        ?string $unitName,
        ?string $catnum
    ) {
        $this->order = $order; // Must be One-To-Many Bidirectional because of unnecessary join table
        $this->name = $name;
        $this->priceWithoutVat = $price->getPriceWithoutVat();
        $this->priceWithVat = $price->getPriceWithVat();
        $this->vatPercent = $vatPercent;
        $this->quantity = $quantity;
        $this->unitName = $unitName;
        $this->catnum = $catnum;
        $this->order->addItem($this); // call after setting attrs for recalc total price
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrder(): \Shopsys\FrameworkBundle\Model\Order\Order
    {
        return $this->order;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPriceWithoutVat(): string
    {
        return $this->priceWithoutVat;
    }

    public function getPriceWithVat(): string
    {
        return $this->priceWithVat;
    }

    public function getVatPercent(): string
    {
        return $this->vatPercent;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitName(): ?string
    {
        return $this->unitName;
    }

    public function getCatnum(): ?string
    {
        return $this->catnum;
    }

    public function getTotalPriceWithVat(): string
    {
        return $this->priceWithVat * $this->quantity;
    }

    public function edit(OrderItemData $orderItemData): void
    {
        $this->name = $orderItemData->name;
        $this->priceWithoutVat = $orderItemData->priceWithoutVat;
        $this->priceWithVat = $orderItemData->priceWithVat;
        $this->vatPercent = $orderItemData->vatPercent;
        $this->quantity = $orderItemData->quantity;
        $this->unitName = $orderItemData->unitName;
        $this->catnum = $orderItemData->catnum;
    }
}
