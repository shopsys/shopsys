<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Product;

/**
 * @ORM\Table(name="product_stocks")
 * @ORM\Entity
 */
class ProductStock
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Stock\Stock
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Stock\Stock")
     * @ORM\JoinColumn(name="stock_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $stock;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE", nullable=false )
     */
    protected $product;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $productQuantity;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\Stock $stock
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    public function __construct(Stock $stock, Product $product)
    {
        $this->stock = $stock;
        $this->product = $product;
        $this->productQuantity = 0;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\ProductStockData $productStockData
     */
    public function edit(ProductStockData $productStockData): void
    {
        $this->productQuantity = $productStockData->productQuantity;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Stock\Stock
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @return int
     */
    public function getProductQuantity()
    {
        return $this->productQuantity;
    }
}
