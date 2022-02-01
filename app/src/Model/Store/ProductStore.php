<?php

declare(strict_types=1);

namespace App\Model\Store;

use App\Model\Product\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="product_stores")
 * @ORM\Entity
 */
class ProductStore
{
    /**
     * @var \App\Model\Product\Product
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE", nullable=false )
     */
    private Product $product;

    /**
     * @var \App\Model\Store\Store
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\App\Model\Store\Store")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private Store $store;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private bool $productExposed;

    /**
     * @param \App\Model\Store\Store $store
     * @param \App\Model\Product\Product $product
     */
    public function __construct(Store $store, Product $product)
    {
        $this->store = $store;
        $this->product = $product;
        $this->productExposed = false;
    }

    /**
     * @param \App\Model\Store\ProductStoreData $productStoreData
     */
    public function edit(ProductStoreData $productStoreData): void
    {
        $this->productExposed = $productStoreData->productExposed;
    }

    /**
     * @return \App\Model\Store\Store
     */
    public function getStore(): Store
    {
        return $this->store;
    }

    /**
     * @return bool
     */
    public function isProductExposed(): bool
    {
        return $this->productExposed;
    }
}
