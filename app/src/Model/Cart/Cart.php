<?php

declare(strict_types=1);

namespace App\Model\Cart;

use App\Model\Cart\Item\CartItem;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Cart\Cart as BaseCart;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;

/**
 * @ORM\Table(name="carts")
 * @ORM\Entity
 * @property \App\Model\Customer\User\CustomerUser|null $customerUser
 * @property \App\Model\Cart\Item\CartItem[]|\Doctrine\Common\Collections\Collection $items
 * @method addItem(\App\Model\Cart\Item\CartItem $item)
 * @method \App\Model\Cart\Item\CartItem[] getItems()
 * @method \App\Model\Cart\Item\CartItem getItemById(int $itemId)
 * @method \App\Model\Cart\Item\CartItem|null findSimilarItemByItem(\App\Model\Cart\Item\CartItem $item)
 */
class Cart extends BaseCart
{
    /**
     * @var \App\Model\Order\PromoCode\PromoCode[]|\Doctrine\Common\Collections\ArrayCollection
     * @ORM\ManyToMany(
     *     targetEntity="\App\Model\Order\PromoCode\PromoCode"
     * )
     * @ORM\JoinTable(name="cart_promo_codes")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $promoCodes;

    /**
     * @param string $cartIdentifier
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     */
    public function __construct(string $cartIdentifier, ?CustomerUser $customerUser = null)
    {
        parent::__construct($cartIdentifier, $customerUser);

        $this->promoCodes = new ArrayCollection();
    }

    /**
     * @inheritDoc
     */
    public function getQuantifiedProducts(): array
    {
        $quantifiedProducts = [];
        foreach ($this->items as $item) {
            $quantifiedProducts[$item->getId()] = new QuantifiedProduct($item->getProduct(), $item->getQuantity());
        }

        return $quantifiedProducts;
    }

    /**
     * @return int
     */
    public function getTotalWeight(): int
    {
        $totalWeight = 0;
        foreach ($this->items as $item) {
            $product = $item->getProduct();
            $totalWeight += $product->getWeight() * $item->getQuantity();
        }

        return $totalWeight;
    }

    /**
     * @param string $itemUuid
     * @return \App\Model\Cart\Item\CartItem
     */
    public function getItemByUuid(string $itemUuid): CartItem
    {
        foreach ($this->items as $item) {
            if ($item->getUuid() === $itemUuid) {
                return $item;
            }
        }

        $message = 'Cart item with UUID "' . $itemUuid . '" not found in cart.';
        throw new InvalidCartItemException($message);
    }
    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     */
    public function applyPromoCode(PromoCode $promoCode): void
    {
        if (!$this->promoCodes->contains($promoCode)) {
            $this->promoCodes->add($promoCode);
            $this->setModifiedNow();
        }
    }
    /**
     * @param string $promoCodeCode
     * @return bool
     */
    public function isPromoCodeApplied(string $promoCodeCode): bool
    {
        return $this->promoCodes->exists(
            static function ($key, PromoCode $promoCode) use ($promoCodeCode) {
                return $promoCode->getCode() === $promoCodeCode;
            }
        );
    }
}
