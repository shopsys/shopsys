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
    protected $product;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    protected $domainId;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $position;
    
    public function __construct(Product $product, int $domainId, int $position)
    {
        $this->product = $product;
        $this->domainId = $domainId;
        $this->position = $position;
    }

    public function getProduct(): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        return $this->product;
    }
}
