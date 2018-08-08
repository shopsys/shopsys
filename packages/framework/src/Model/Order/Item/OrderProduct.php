<?php

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Product;

/**
 * @ORM\Entity
 */
class OrderProduct extends OrderItem
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(nullable=true, name="product_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $product;

    public function __construct(
        Order $order,
        string $name,
        Price $price,
        string $vatPercent,
        int $quantity,
        ?string $unitName,
        ?string $catnum,
        Product $product = null
    ) {
        parent::__construct(
            $order,
            $name,
            $price,
            $vatPercent,
            $quantity,
            $unitName,
            $catnum
        );

        if ($product !== null && $product->isMainVariant()) {
            throw new \Shopsys\FrameworkBundle\Model\Order\Item\Exception\MainVariantCannotBeOrderedException();
        }

        $this->product = $product;
    }

    public function getProduct(): ?\Shopsys\FrameworkBundle\Model\Product\Product
    {
        return $this->product;
    }

    public function hasProduct(): bool
    {
        return $this->product !== null;
    }
}
