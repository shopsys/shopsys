<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * Remembers that the customer manually removed the gift of the main product from the cart,
 * so the gift is not added again until the customer restores it
 */
#[AsMcpTable]
#[ORM\Table(name: 'cart_removed_product_gifts')]
#[ORM\UniqueConstraint(name: 'unique_cart_removed_product_gift', columns: ['cart_id', 'main_product_id', 'gift_product_id'])]
#[ORM\Entity]
class CartRemovedProductGift
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Cart
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'cart_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'removedProductGifts')]
    protected $cart;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'main_product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    protected $mainProduct;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'gift_product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    protected $giftProduct;

    public function __construct(Cart $cart, Product $mainProduct, Product $giftProduct)
    {
        $this->cart = $cart;
        $this->mainProduct = $mainProduct;
        $this->giftProduct = $giftProduct;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getMainProduct()
    {
        return $this->mainProduct;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getGiftProduct()
    {
        return $this->giftProduct;
    }
}
