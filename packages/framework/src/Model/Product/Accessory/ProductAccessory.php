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

    /**
     * @param int $position
     */
    public function __construct(Product $product, Product $accessory, $position)
    {
        $this->product = $product;
        $this->accessory = $accessory;
        $this->position = $position;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getAccessory()
    {
        return $this->accessory;
    }

    public function getPosition()
    {
        return $this->position;
    }
}
