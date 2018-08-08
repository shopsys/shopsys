<?php

namespace Tests\ShopBundle\Database\EntityExtension\Model;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Product;

/**
 * @ORM\Entity
 *
 * Base of this class is a copy of OrderProduct entity from FrameworkBundle
 * Reason is described in /project-base/docs/wip_glassbox/entity-extension.md
 *
 * @see \Shopsys\FrameworkBundle\Model\Order\Item\OrderProduct
 */
class ExtendedOrderProduct extends ExtendedOrderItem
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(nullable=true, name="product_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $product;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $productStringField;

    public function __construct(
        Order $order,
        string $name,
        Price $price,
        string $vatPercent,
        int $quantity,
        string $unitName,
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

    public function getProductStringField(): ?string
    {
        return $this->productStringField;
    }

    public function setProductStringField(?string $productStringField): void
    {
        $this->productStringField = $productStringField;
    }
}
