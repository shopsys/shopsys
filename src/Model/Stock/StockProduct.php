<?php

declare(strict_types=1);


namespace App\Model\Stock;


use App\Model\Product\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="stocks_product")
 * @ORM\Entity
 */
class StockProduct
{
    /**
     * @var \App\Model\Stock\Stock|null
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\App\Model\Stock\Stock",inversedBy="stockProducts")
     * @ORM\JoinColumn(name="stock_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $stock;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Product", inversedBy="stockProducts")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="SET NULL", nullable=true )
     *
     */
    protected $product;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $productQuantity;

    public function __construct(Stock $stock, Product $product)
    {
        $this->stock = $stock;
        $this->product = $product;
        $this->productQuantity = 0;
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