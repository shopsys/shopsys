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
     * @var \App\Model\Stock\StockFacadeInterface
     */
    private $stockFacade;
    /**
     * @var \App\Model\Stock\StockProductFactory
     */
    private $stockProductFactory;

    public function __construct(
        StockProductRepository $stockProductRepository,
        StockFacadeInterface $stockFacade,
        StockProductFactory $stockProductFactory
    )
    {
        $this->stockProductRepository = $stockProductRepository;
        $this->stockFacade = $stockFacade;
        $this->stockProductFactory = $stockProductFactory;
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