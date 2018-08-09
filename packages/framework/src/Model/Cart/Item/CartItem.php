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

    /**
     * @param int $quantity
     * @param string $watchedPrice
     */
    public function __construct(
        CustomerIdentifier $customerIdentifier,
        Product $product,
        $quantity,
        $watchedPrice
    ) {
        $this->cartIdentifier = $customerIdentifier->getCartIdentifier();
        $this->user = $customerIdentifier->getUser();
        $this->product = $product;
        $this->watchedPrice = $watchedPrice;
        $this->changeQuantity($quantity);
        $this->addedAt = new DateTime();
    }

    /**
     * @param int $newQuantity
     */
    public function changeQuantity($newQuantity)
    {
        if (filter_var($newQuantity, FILTER_VALIDATE_INT) === false || $newQuantity <= 0) {
            throw new \Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidQuantityException($newQuantity);
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
            throw new \Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException();
        }

        return $this->product;
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getName($locale = null)
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
     * @return string|null
     */
    public function getWatchedPrice()
    {
        return $this->watchedPrice;
    }

    /**
     * @param string|null $watchedPrice
     */
    public function setWatchedPrice($watchedPrice)
    {
        $this->watchedPrice = $watchedPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem $cartItem
     * @return bool
     */
    public function isSimilarItemAs(self $cartItem)
    {
        return $this->getProduct()->getId() === $cartItem->getProduct()->getId();
    }

    public function getCartIdentifier()
    {
        return $this->cartIdentifier;
    }

    /**
     * @return \DateTime
     */
    public function getAddedAt()
    {
        return $this->addedAt;
    }

    public function changeAddedAt(DateTime $addedAt)
    {
        $this->addedAt = $addedAt;
    }
}
