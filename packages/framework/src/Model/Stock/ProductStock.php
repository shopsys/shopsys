<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'product_stocks')]
#[ORM\Entity]
class ProductStock
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Stock\Stock
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'stock_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Stock::class)]
    protected $stock;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    protected $product;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $productQuantity;

    public function __construct(Stock $stock, Product $product)
    {
        $this->stock = $stock;
        $this->product = $product;
        $this->productQuantity = 0;
    }

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
