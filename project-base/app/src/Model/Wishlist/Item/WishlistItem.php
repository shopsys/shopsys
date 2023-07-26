<?php

declare(strict_types=1);

namespace App\Model\Wishlist\Item;

use App\Model\Product\Product;
use App\Model\Wishlist\Wishlist;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="wishlist_items")
 * @ORM\Entity
 */
class WishlistItem
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private DateTime $createdAt;

    /**
     * @var \App\Model\Product\Product
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Product")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Product $product;

    /**
     * @var \App\Model\Wishlist\Wishlist
     * @ORM\ManyToOne(targetEntity="App\Model\Wishlist\Wishlist", inversedBy="items", cascade={"persist"})
     * @ORM\JoinColumn(name="wishlist_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private Wishlist $wishlist;

    /**
     * @param \App\Model\Wishlist\Wishlist $wishlist
     * @param \App\Model\Product\Product $product
     */
    public function __construct(Wishlist $wishlist, Product $product)
    {
        $this->wishlist = $wishlist;
        $this->product = $product;
        $this->createdAt = new DateTime();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return \App\Model\Product\Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @return \App\Model\Wishlist\Wishlist
     */
    public function getWishlist(): Wishlist
    {
        return $this->wishlist;
    }
}
