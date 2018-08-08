<?php

namespace Shopsys\FrameworkBundle\Model\Cart\Item;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier;
use Shopsys\FrameworkBundle\Model\Product\Product;

/**
 * @ORM\Table(name="cart_items")
 * @ORM\Entity
 */
class CartItem
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
     * @var string
     *
     * @ORM\Column(type="string", length=127)
     */
    protected $cartIdentifier;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User|null
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable = true, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(nullable=true, name="product_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $product;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $quantity;

    /**
     * @var string|null
     *
     * @ORM\Column(type="decimal", precision=20, scale=6, nullable=true)
     */
    protected $watchedPrice;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $addedAt;
    
    public function __construct(
        CustomerIdentifier $customerIdentifier,
        Product $product,
        int $quantity,
        string $watchedPrice
    ) {
        $this->cartIdentifier = $customerIdentifier->getCartIdentifier();
        $this->user = $customerIdentifier->getUser();
        $this->product = $product;
        $this->watchedPrice = $watchedPrice;
        $this->changeQuantity($quantity);
        $this->addedAt = new DateTime();
    }
    
    public function changeQuantity(int $newQuantity): void
    {
        if (filter_var($newQuantity, FILTER_VALIDATE_INT) === false || $newQuantity <= 0) {
            throw new \Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidQuantityException($newQuantity);
        }

        $this->quantity = $newQuantity;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProduct(): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        if ($this->product === null) {
            throw new \Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException();
        }

        return $this->product;
    }

    /**
     * @param string|null $locale
     */
    public function getName(?string $locale = null): ?string
    {
        return $this->getProduct()->getName($locale);
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getWatchedPrice(): ?string
    {
        return $this->watchedPrice;
    }

    /**
     * @param string|null $watchedPrice
     */
    public function setWatchedPrice(?string $watchedPrice): void
    {
        $this->watchedPrice = $watchedPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem $cartItem
     */
    public function isSimilarItemAs(self $cartItem): bool
    {
        return $this->getProduct()->getId() === $cartItem->getProduct()->getId();
    }

    public function getCartIdentifier(): string
    {
        return $this->cartIdentifier;
    }

    public function getAddedAt(): \DateTime
    {
        return $this->addedAt;
    }

    public function changeAddedAt(DateTime $addedAt): void
    {
        $this->addedAt = $addedAt;
    }
}
