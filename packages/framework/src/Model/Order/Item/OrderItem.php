<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Doctrine\ORM\Mapping as ORM;
use Litipk\BigNumbers\Decimal;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\EntityLogIdentify;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\Loggable;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableChild;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableParentProperty;
use Shopsys\FrameworkBundle\Model\Order\Item\Exception\MainVariantCannotBeOrderedException;
use Shopsys\FrameworkBundle\Model\Order\Item\Exception\OrderItemHasOnlyOneTotalPriceException;
use Shopsys\FrameworkBundle\Model\Order\Item\Exception\WrongItemTypeException;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

/**
 * @ORM\Table(name="order_items")
 * @ORM\Entity
 */
#[LoggableChild(Loggable::STRATEGY_INCLUDE_ALL)]
class OrderItem
{
    public const string TYPE_PAYMENT = 'payment';
    public const string TYPE_PRODUCT = 'product';
    public const string TYPE_TRANSPORT = 'transport';
    public const string TYPE_DISCOUNT = 'discount';
    public const string TYPE_ROUNDING = 'rounding';

    /**
     * @var int|null
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Order
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Order\Order", inversedBy="items")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    #[LoggableParentProperty]
    protected $order;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $name;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    protected $priceWithoutVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    protected $priceWithVat;

    /**
     * This property can be used when order item has prices that differ from current price calculation implementation.
     * Otherwise it should be set to NULL (which means it will be calculated automatically).
     *
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     * @ORM\Column(type="money", precision=20, scale=6, nullable=true)
     */
    protected $totalPriceWithoutVat;

    /**
     * This property can be used when order item has prices that differ from current price calculation implementation.
     * Otherwise it should be set to NULL (which means it will be calculated automatically).
     *
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     * @ORM\Column(type="money", precision=20, scale=6, nullable=true)
     */
    protected $totalPriceWithVat;

    /**
     * @var string
     * @ORM\Column(type="decimal", precision=20, scale=6)
     */
    protected $vatPercent;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $quantity;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $unitName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $catnum;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Transport\Transport")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $transport;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Payment\Payment")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $payment;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(nullable=true, name="product_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $product;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param string $name
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @param string $vatPercent
     * @param int $quantity
     * @param string $type
     * @param string|null $unitName
     * @param string|null $catnum
     */
    public function __construct(
        Order $order,
        $name,
        Price $price,
        $vatPercent,
        $quantity,
        $type,
        $unitName,
        $catnum,
    ) {
        $this->order = $order; // Must be One-To-Many Bidirectional because of unnecessary join table
        $this->name = $name;
        $this->priceWithoutVat = $price->getPriceWithoutVat();
        $this->priceWithVat = $price->getPriceWithVat();
        $this->vatPercent = Decimal::create($vatPercent, 6)->innerValue();
        $this->quantity = $quantity;
        $this->type = $type;
        $this->unitName = $unitName;
        $this->catnum = $catnum;
        $this->order->addItem($this); // call after setting attrs for recalc total price
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return string
     */
    #[EntityLogIdentify]
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPriceWithoutVat()
    {
        return $this->priceWithoutVat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPriceWithVat()
    {
        return $this->priceWithVat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getTotalPriceWithoutVat()
    {
        return $this->hasForcedTotalPrice() ? $this->totalPriceWithoutVat : null;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getTotalPriceWithVat()
    {
        return $this->hasForcedTotalPrice() ? $this->totalPriceWithVat : $this->priceWithVat->multiply(
            $this->quantity,
        );
    }

    /**
     * The total price property can be used when order item has prices that differ from current price calculation implementation.
     * Otherwise it should be set to NULL (which means it will be calculated automatically).
     *
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $totalPrice
     */
    public function setTotalPrice(?Price $totalPrice): void
    {
        $this->totalPriceWithVat = $totalPrice?->getPriceWithVat();
        $this->totalPriceWithoutVat = $totalPrice?->getPriceWithoutVat();
    }

    /**
     * @return bool
     */
    public function hasForcedTotalPrice(): bool
    {
        if ($this->totalPriceWithVat === null xor $this->totalPriceWithoutVat === null) {
            throw new OrderItemHasOnlyOneTotalPriceException($this->totalPriceWithVat, $this->totalPriceWithoutVat);
        }

        return $this->totalPriceWithoutVat !== null && $this->totalPriceWithVat !== null;
    }

    /**
     * @return string
     */
    public function getVatPercent()
    {
        return $this->vatPercent;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return string|null
     */
    public function getUnitName()
    {
        return $this->unitName;
    }

    /**
     * @return string|null
     */
    public function getCatnum()
    {
        return $this->catnum;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $orderItemData
     */
    public function edit(OrderItemData $orderItemData)
    {
        $this->name = $orderItemData->name;
        $this->priceWithoutVat = $orderItemData->priceWithoutVat;
        $this->priceWithVat = $orderItemData->priceWithVat;

        if ($orderItemData->usePriceCalculation) {
            $this->setTotalPrice(null);
        } else {
            $this->setTotalPrice(new Price($orderItemData->totalPriceWithoutVat, $orderItemData->totalPriceWithVat));
        }

        $this->vatPercent = Decimal::create($orderItemData->vatPercent, 6)->innerValue();
        $this->quantity = $orderItemData->quantity;
        $this->unitName = $orderItemData->unitName;
        $this->catnum = $orderItemData->catnum;

        if ($this->isTypeTransport()) {
            $this->transport = $orderItemData->transport;
        }

        if ($this->isTypePayment()) {
            $this->payment = $orderItemData->payment;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     */
    public function setTransport($transport): void
    {
        $this->checkTypeTransport();
        $this->transport = $transport;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport
     */
    public function getTransport()
    {
        $this->checkTypeTransport();

        return $this->transport;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     */
    public function setPayment($payment): void
    {
        $this->checkTypePayment();
        $this->payment = $payment;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    public function getPayment()
    {
        $this->checkTypePayment();

        return $this->payment;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    public function getProduct()
    {
        $this->checkTypeProduct();

        return $this->product;
    }

    /**
     * @return bool
     */
    public function hasProduct()
    {
        $this->checkTypeProduct();

        return $this->product !== null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|null $product
     */
    public function setProduct($product): void
    {
        $this->checkTypeProduct();

        if ($product !== null && $product->isMainVariant()) {
            throw new MainVariantCannotBeOrderedException();
        }

        $this->product = $product;
    }

    /**
     * @return bool
     */
    public function isTypeProduct(): bool
    {
        return $this->type === self::TYPE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isTypePayment(): bool
    {
        return $this->type === self::TYPE_PAYMENT;
    }

    /**
     * @return bool
     */
    public function isTypeTransport(): bool
    {
        return $this->type === self::TYPE_TRANSPORT;
    }

    /**
     * @return bool
     */
    public function isTypeDiscount(): bool
    {
        return $this->type === self::TYPE_DISCOUNT;
    }

    /**
     * @return bool
     */
    public function isTypeRounding(): bool
    {
        return $this->type === self::TYPE_ROUNDING;
    }

    /**
     * @param string $type
     */
    protected function checkTypeOf(string $type): void
    {
        if ($this->type !== $type) {
            throw new WrongItemTypeException($type, $this->type);
        }
    }

    protected function checkTypeTransport(): void
    {
        $this->checkTypeOf(self::TYPE_TRANSPORT);
    }

    protected function checkTypePayment(): void
    {
        $this->checkTypeOf(self::TYPE_PAYMENT);
    }

    protected function checkTypeProduct(): void
    {
        $this->checkTypeOf(self::TYPE_PRODUCT);
    }

    protected function checkTypeDiscount(): void
    {
        $this->checkTypeOf(self::TYPE_DISCOUNT);
    }

    protected function checkTypeRounding(): void
    {
        $this->checkTypeOf(self::TYPE_DISCOUNT);
    }
}
