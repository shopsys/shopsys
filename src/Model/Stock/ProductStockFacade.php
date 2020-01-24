<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Model\Product\Product;

class ProductStockFacade implements ProductStockFacadeInterface
{
    /**
     * @var \App\Model\Stock\ProductStockRepository
     */
    private $productStockRepository;

    /**
     * @param \App\Model\Stock\ProductStockRepository $stockProductRepository
     */
    public function __construct(
        ProductStockRepository $stockProductRepository
    ) {
        $this->productStockRepository = $stockProductRepository;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Stock\ProductStock[]
     */
    public function getProductsStockByProduct(Product $product): array
    {
        return $this->productStockRepository->getProductStockByProduct($product);
    }
}
