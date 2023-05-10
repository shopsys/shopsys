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
     * @var \App\Model\Stock\Stock
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\App\Model\Stock\Stock")
     * @ORM\JoinColumn(name="stock_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $stock;

    /**
     * @var \App\Model\Product\Product
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE", nullable=false )
     */
    protected $product;

    /**
     * @var int
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
    }

    /**
     * @param \App\Model\Stock\ProductStockData $productStockData
     */
    public function edit(ProductStockData $productStockData): void
    {
        $this->productQuantity = $productStockData->productQuantity;
    }

    /**
     * @return \App\Model\Stock\Stock
     */
    public function getStock(): Stock
    {
        return $this->stock;
    }

    /**
     * @return int
     */
    public function getProductQuantity(): int
    {
        return $this->productQuantity;
    }
}
