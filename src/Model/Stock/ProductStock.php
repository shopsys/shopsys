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
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\App\Model\Stock\Stock")
     * @ORM\JoinColumn(name="stock_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $stock;

    /**
     * @var \App\Model\Product\Product
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Product")
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
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $productExposed;

    /**
     * @param \App\Model\Stock\Stock $stock
     * @param \App\Model\Product\Product $product
     */
    public function __construct(Stock $stock, Product $product)
    {
        $this->stock = $stock;
        $this->product = $product;
        $this->productQuantity = 0;
        $this->productExposed = false;
    }

    /**
     * @param \App\Model\Stock\ProductStockData $productStockData
     */
    public function edit(ProductStockData $productStockData): void
    {
        $this->productQuantity = $productStockData->productQuantity;
        $this->productExposed = $productStockData->productExposed;
    }

    /**
     * @return \App\Model\Stock\Stock
     */
    public function getStock(): Stock
    {
        return $this->stock;
    }

    /**
     * @return \App\Model\Product\Product|null
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /**
     * @param \App\Model\Product\Product $product
     */
    public function setProduct(Product $product): void
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

    /**
     * @return bool
     */
    public function isProductExposed(): bool
    {
        return $this->productExposed;
    }
}
