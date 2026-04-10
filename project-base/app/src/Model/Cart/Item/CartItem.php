<?php

declare(strict_types=1);

namespace App\Model\Cart\Item;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem as BaseCartItem;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @property \App\Model\Product\Product|null $product
 * @method \App\Model\Product\Product getProduct()
 * @method bool isSimilarItemAs(\App\Model\Cart\Item\CartItem $cartItem)
 * @property \App\Model\Cart\Cart $cart
 * @method __construct(\App\Model\Cart\Cart $cart, \App\Model\Product\Product $product, int $quantity, \Shopsys\FrameworkBundle\Component\Money\Money|null $watchedPrice, string $type = \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemTypeEnum::TYPE_PRODUCT)
 */
#[AsMcpTable]
#[ORM\Table(name: 'cart_items')]
#[ORM\Entity]
class CartItem extends BaseCartItem
{
    #[Override]
    public function getName(?string $locale = null): ?string
    {
        return $this->getProduct()->getFullName($locale);
    }
}
