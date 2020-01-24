<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;

class ProductStockFactory
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \App\Model\Stock\ProductStockRepository
     */
    private $productStockRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \App\Model\Stock\ProductStockRepository $productStockRepository
     */
    public function __construct(EntityManagerInterface $entityManager, ProductStockRepository $productStockRepository)
    {
        $this->em = $entityManager;
        $this->productStockRepository = $productStockRepository;
    }

    /**
     * @param \App\Model\Stock\Stock $stock
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Stock\ProductStock
     */
    public function findOrCreateProductStock(Stock $stock, Product $product): ProductStock
    {
        $productStock = $this->productStockRepository->getProductStockByStockAndProduct($stock, $product);
        if (!$productStock) {
            $productStock = new ProductStock($stock, $product);
            $this->em->persist($productStock);
        }
        return $productStock;
    }
}
