<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;

class ProductStockFacade
{
    /**
     * @var \App\Model\Stock\ProductStockRepository
     */
    private $productStockRepository;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @param \App\Model\Stock\ProductStockRepository $stockProductRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        ProductStockRepository $stockProductRepository,
        EntityManagerInterface $em
    ) {
        $this->productStockRepository = $stockProductRepository;
        $this->em = $em;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Stock\ProductStock[]
     */
    public function getProductStocksByProduct(Product $product): array
    {
        return $this->productStockRepository->getProductStocksByProduct($product);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Stock\Stock $stock
     * @param int $productQuantity
     */
    public function setProductStockQuantity(Product $product, Stock $stock, int $productQuantity): void
    {
        $productStock = $this->productStockRepository->findProductStockByStockAndProduct($stock, $product);
        if (!$productStock) {
            $productStock = new ProductStock($stock, $product);
            $this->em->persist($productStock);
        }
        $productStock->setProductQuantity($productQuantity);
        $this->em->flush();
    }
}
