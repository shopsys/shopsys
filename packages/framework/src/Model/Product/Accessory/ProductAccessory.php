<?php

namespace Shopsys\FrameworkBundle\Model\Product\Accessory;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Product;

/**
 * @ORM\Table(name="product_accessories")
 * @ORM\Entity
 */
class ProductAccessory
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(nullable=false, name="product_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    protected $product;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(nullable=false, name="accessory_product_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    protected $accessory;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $position;

    public function __construct(Product $product, Product $accessory, int $position)
    {
        $this->product = $product;
        $this->accessory = $accessory;
        $this->position = $position;
    }

    public function getProduct(): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        return $this->product;
    }

    public function getAccessory(): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        return $this->accessory;
    }

    public function getPosition(): int
    {
        return $this->position;
    }
}
