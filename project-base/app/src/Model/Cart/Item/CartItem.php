<?php

declare(strict_types=1);

namespace App\Model\Cart\Item;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem as BaseCartItem;
use Shopsys\FrameworkBundle\Model\Product\Product;

/**
 * @ORM\Table(name="cart_items")
 * @ORM\Entity
 * @property \App\Model\Product\Product|null $product
 * @method \App\Model\Product\Product getProduct()
 * @method bool isSimilarItemAs(\App\Model\Cart\Item\CartItem $cartItem)
 * @property \App\Model\Cart\Cart $cart
 */
class CartItem extends BaseCartItem
{
    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    private string $uuid;

    /**
     * @param \App\Model\Cart\Cart $cart
     * @param \App\Model\Product\Product $product
     * @param int $quantity
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $watchedPrice
     */
    public function __construct(
        Cart $cart,
        Product $product,
        int $quantity,
        ?Money $watchedPrice,
    ) {
        parent::__construct($cart, $product, $quantity, $watchedPrice);

        $this->uuid = Uuid::uuid4()->toString();
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getName(?string $locale = null): ?string
    {
        return $this->getProduct()->getFullname($locale);
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return bool
     */
    public function hasProduct(): bool
    {
        return $this->product !== null;
    }
}
