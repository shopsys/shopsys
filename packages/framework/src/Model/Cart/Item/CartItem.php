<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Item;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidQuantityException;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\Clock\DatePoint;

#[ORM\Table(name: 'cart_items')]
#[ORM\Entity]
class CartItem
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Cart
     */
    #[ORM\JoinColumn(name: 'cart_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'items', cascade: ['persist'])]
    protected $cart;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    #[ORM\JoinColumn(nullable: true, name: 'product_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    protected $product;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    protected $quantity;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    #[ORM\Column(type: 'money', precision: 20, scale: 6, nullable: true)]
    protected $watchedPrice;

    /**
     * @var \DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable')]
    protected $addedAt;

    /**
     * @var string
     */
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 32, nullable: false)]
    protected $type;

    public function __construct(
        Cart $cart,
        Product $product,
        int $quantity,
        ?Money $watchedPrice,
        string $type = CartItemTypeEnum::TYPE_PRODUCT,
    ) {
        $this->cart = $cart;
        $this->product = $product;
        $this->setWatchedPrice($watchedPrice);
        $this->changeQuantity($quantity);
        $this->addedAt = new DatePoint();
        $this->uuid = Uuid::uuid4()->toString();
        $this->setType($type);
    }

    public function changeQuantity(int $newQuantity): void
    {
        if ($newQuantity <= 0) {
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

    public function isSimilarItemAs(self $cartItem): bool
    {
        return $this->getProduct()->getId() === $cartItem->getProduct()->getId() && $this->getType() === $cartItem->getType();
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getAddedAt()
    {
        return $this->addedAt;
    }

    public function setAddedAtToNow(): void
    {
        $this->addedAt = new DatePoint();
    }

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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    protected function setType($type): void
    {
        $this->type = $type;
    }

    public function isProductGift(): bool
    {
        return $this->type === CartItemTypeEnum::TYPE_PRODUCT_GIFT;
    }

    public function isProduct(): bool
    {
        return $this->type === CartItemTypeEnum::TYPE_PRODUCT;
    }

    public function getFreeQuantity(int $domainId): int
    {
        $product = $this->getProduct();

        return $product->calculateFreeQuantity($this->getQuantity(), $domainId);
    }
}
