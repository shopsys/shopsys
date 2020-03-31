<?php

declare(strict_types=1);

namespace App\Model\Product\Series;

use App\Model\Product\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="product_series_products")
 * @ORM\Entity
 */
class ProductSeriesProduct
{
    /**
     * @var \App\Model\Product\Series\ProductSeries
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Series\ProductSeries")
     * @ORM\JoinColumn(name="product_series_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $productSeries;

    /**
     * @var \App\Model\Product\Product
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE", nullable=false )
     */
    protected $product;

    /**
     * @param \App\Model\Product\Series\ProductSeries $productSeries
     * @param \App\Model\Product\Product $product
     */
    public function __construct(ProductSeries $productSeries, Product $product)
    {
        $this->productSeries = $productSeries;
        $this->product = $product;
    }

    /**
     * @return \App\Model\Product\Series\ProductSeries
     */
    public function getProductSeries(): \App\Model\Product\Series\ProductSeries
    {
        return $this->productSeries;
    }

    /**
     * @return \App\Model\Product\Product
     */
    public function getProduct(): \App\Model\Product\Product
    {
        return $this->product;
    }
}
