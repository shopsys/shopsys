<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Item;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidQuantityException;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Webmozart\Assert\Assert;

/**
 * @ORM\Table(name="cart_items")
 * @ORM\Entity
 */
class CartItem
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Cart
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Cart\Cart", inversedBy="items", cascade={"persist"})
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $cart;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(nullable=true, name="product_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $product;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $quantity;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     * @ORM\Column(type="money", precision=20, scale=6, nullable=true)
     */
    protected $watchedPrice;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $addedAt;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     * @ORM\Column(type="money", precision=20, scale=6, nullable=true)
     */
    protected $unitPriceWithoutVatAtAddition;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     * @ORM\Column(type="money", precision=20, scale=6, nullable=true)
     */
    protected $unitPriceWithVatAtAddition;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $quantity
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $watchedPrice
     */
    public function __construct(
        Cart $cart,
        Product $product,
        int $quantity,
        ?Money $watchedPrice,
    ) {
        $this->cart = $cart;
        $this->product = $product;
        $this->setWatchedPrice($watchedPrice);
        $this->changeQuantity($quantity);
        $this->addedAt = new DateTime();
        $this->uuid = Uuid::uuid4()->toString();
    }

    /**
     * @param int $newQuantity
     */
    public function changeQuantity(int $newQuantity): void
    {
        if (Assert::integer($newQuantity) === false || $newQuantity <= 0) {
            throw new InvalidQuantityException($newQuantity);
        }

        $this->quantity = $newQuantity;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct()
    {
        if ($this->product === null) {
            throw new ProductNotFoundException();
        }

        return $this->product;
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getName(?string $locale = null): ?string
    {
        return $this->getProduct()->getName($locale);
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getWatchedPrice()
    {
        return $this->watchedPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $watchedPrice
     */
    public function setWatchedPrice($watchedPrice): void
    {
        $this->watchedPrice = $watchedPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem $cartItem
     * @return bool
     */
    public function isSimilarItemAs(self $cartItem): bool
    {
        return $this->getProduct()->getId() === $cartItem->getProduct()->getId();
    }

    /**
     * @return \DateTime
     */
    public function getAddedAt()
    {
        return $this->addedAt;
    }

    /**
     * @param \DateTime $addedAt
     */
    public function changeAddedAt(DateTime $addedAt): void
    {
        $this->addedAt = $addedAt;
    }

    /**
     * @return bool
     */
    public function hasProduct(): bool
    {
        return $this->product !== null;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $unitPrice
     */
    public function setUnitPricesAtAddition($unitPrice): void
    {
        $this->unitPriceWithoutVatAtAddition = $unitPrice->getPriceWithoutVat();
        $this->unitPriceWithVatAtAddition = $unitPrice->getPriceWithVat();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function getTotalPriceBeforePromotion()
    {
        $withoutVat = $this->unitPriceWithoutVatAtAddition?->multiply($this->getQuantity()) ?? Money::zero();
        $withVat = $this->unitPriceWithVatAtAddition?->multiply($this->getQuantity()) ?? Money::zero();

        return new \Shopsys\FrameworkBundle\Model\Pricing\Price($withoutVat, $withVat);
    }

    /**
     * @return int
     */
    public function getFreeQuantity(): int
    {
        $product = $this->getProduct();

        if ($product->getPromotionX() === null || $product->getPromotionY() === null) {
            return 0;
        }

        $x = (int)$product->getPromotionX();
        $y = (int)$product->getPromotionY();

        if ($x <= 0 || $y <= 0) {
            return 0;
        }

        $q = $this->getQuantity();
        $group = $x + $y;
        $fullGroups = intdiv($q, $group);
        $remainder = $q % $group;
        $extra = max(0, min($remainder - $x, $y));

        return (int)($fullGroups * $y + $extra);
    }

    /**
     * @return int
     */
    public function getPaidQuantity(): int
    {
        return $this->getQuantity() - $this->getFreeQuantity();
    }
}
