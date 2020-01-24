<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Model\Product\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="product_stocks")
 * @ORM\Entity
 */
class ProductStock
{
    /**
     * @var \App\Model\Stock\Stock|null
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\App\Model\Stock\Stock",inversedBy="productStocks")
     * @ORM\JoinColumn(name="stock_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $stock;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Product", inversedBy="productStocks")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE", nullable=false )
     */
    protected $product;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $productQuantity;

    /**
     * @param \App\Model\Stock\Stock $stock
     * @param \App\Model\Product\Product $product
     */
    public function __construct(Stock $stock, Product $product)
    {
        $this->stock = $stock;
        $this->product = $product;
        $this->productQuantity = 0;
        $product->addProductStock($this);
    }

    /**
     * @return \App\Model\Stock\Stock
     */
    public function getStock(): \App\Model\Stock\Stock
    {
        return $this->stock;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    public function getProduct(): ?\Shopsys\FrameworkBundle\Model\Product\Product
    {
        return $this->product;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    public function setProduct(\Shopsys\FrameworkBundle\Model\Product\Product $product): void
    {
        $this->product = $product;
    }

    /**
     * @return int
     */
    public function getProductQuantity(): int
    {
        return $this->productQuantity;
    }

    /**
     * @param int $productQuantity
     */
    public function setProductQuantity(int $productQuantity): void
    {
        $this->productQuantity = $productQuantity;
    }
}
