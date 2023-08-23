<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;

/**
 * @ORM\Table(name="carts")
 * @ORM\Entity
 */
class Cart
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=127)
     */
    protected $cartIdentifier;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser")
     * @ORM\JoinColumn(name="customer_user_id", referencedColumnName="id", nullable = true, onDelete="CASCADE")
     */
    protected $customerUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[]|\Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(
     *     targetEntity="Shopsys\FrameworkBundle\Model\Cart\Item\CartItem",
     *     mappedBy="cart"
     * )
     * @ORM\OrderBy({"id" = "DESC"})
     */
    protected $items;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $modifiedAt;

    /**
     * @param string $cartIdentifier
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     */
    public function __construct(string $cartIdentifier, ?CustomerUser $customerUser = null)
    {
        $this->cartIdentifier = $cartIdentifier;
        $this->customerUser = $customerUser;
        $this->items = new ArrayCollection();
        $this->modifiedAt = new DateTime();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem $item
     */
    public function addItem(CartItem $item)
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $this->setModifiedNow();
        }
    }

    /**
     * @param int $itemId
     */
    public function removeItemById($itemId)
    {
        foreach ($this->items as $item) {
            if ($item->getId() === $itemId) {
                $this->items->removeElement($item);
                $this->setModifiedNow();

                return;
            }
        }
        $message = 'Cart item with ID = ' . $itemId . ' is not in cart for remove.';

        throw new InvalidCartItemException($message);
    }

    public function clean()
    {
        $this->items->clear();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[]
     */
    public function getItems()
    {
        return $this->items->getValues();
    }

    /**
     * @return int
     */
    public function getItemsCount()
    {
        return $this->items->count();
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->getItemsCount() === 0;
    }

    /**
     * @param array $quantitiesByItemId
     */
    public function changeQuantities(array $quantitiesByItemId)
    {
        foreach ($this->items as $item) {
            if (array_key_exists($item->getId(), $quantitiesByItemId)) {
                $item->changeQuantity((int)$quantitiesByItemId[$item->getId()]);
            }
        }

        $this->setModifiedNow();
    }

    /**
     * @param int $itemId
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem
     */
    public function getItemById($itemId)
    {
        foreach ($this->items as $item) {
            if ($item->getId() === $itemId) {
                return $item;
            }
        }
        $message = 'CartItem with id = ' . $itemId . ' not found in cart.';

        throw new InvalidCartItemException($message);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[]
     */
    public function getQuantifiedProducts()
    {
        $quantifiedProducts = [];

        foreach ($this->items as $item) {
            $quantifiedProducts[] = new QuantifiedProduct($item->getProduct(), $item->getQuantity());
        }

        return $quantifiedProducts;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem $item
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem|null
     */
    public function findSimilarItemByItem(CartItem $item): ?CartItem
    {
        foreach ($this->items as $similarItem) {
            if ($similarItem->isSimilarItemAs($item)) {
                return $similarItem;
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getCartIdentifier()
    {
        return $this->cartIdentifier;
    }

    public function setModifiedNow(): void
    {
        $this->modifiedAt = new DateTime();
    }

    /**
     * @param \DateTime $modifiedAt
     */
    public function setModifiedAt(DateTime $modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public function getCustomerUser(): ?CustomerUser
    {
        return $this->customerUser;
    }
}
