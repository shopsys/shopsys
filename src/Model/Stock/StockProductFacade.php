<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Model\Product\Product;

class StockProductFacade implements StockProductFacadeInterface
{
    /**
     * @var \App\Model\Stock\StockProductRepository
     */
    private $stockProductRepository;

    /**
     * @param \App\Model\Stock\StockProductRepository $stockProductRepository
     */
    public function __construct(
        StockProductRepository $stockProductRepository
    ) {
        $this->stockProductRepository = $stockProductRepository;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Stock\StockProduct[]
     */
    public function getStockProductsByProduct(Product $product): array
    {
        return $this->stockProductRepository->getStockProductByProduct($product);
    }
}
