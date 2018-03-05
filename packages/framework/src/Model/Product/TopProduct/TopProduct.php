<?php

namespace Shopsys\FrameworkBundle\Model\Product\TopProduct;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Product;

/**
 * @ORM\Table(name="products_top")
 * @ORM\Entity
 */
class TopProduct
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(nullable=false, name="product_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    private $product;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    private $domainId;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param int $position
     */
    public function __construct(Product $product, $domainId, $position)
    {
        $this->product = $product;
        $this->domainId = $domainId;
        $this->position = $position;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct()
    {
        return $this->product;
    }
}
