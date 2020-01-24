<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;

class ProductStockFacade implements ProductStockFacadeInterface
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
     * @var \App\Model\Stock\ProductStockFactory
     */
    private $productStockFactory;

    /**
     * @param \App\Model\Stock\ProductStockRepository $stockProductRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Stock\ProductStockFactory $productStockFactory
     */
    public function __construct(
        ProductStockRepository $stockProductRepository,
        EntityManagerInterface $em,
        ProductStockFactory $productStockFactory
    ) {
        $this->productStockRepository = $stockProductRepository;
        $this->em = $em;
        $this->productStockFactory = $productStockFactory;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Stock\ProductStock[]
     */
    public function getProductsStockByProduct(Product $product): array
    {
        return $this->productStockRepository->getProductStockByProduct($product);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Stock\Stock $stock
     * @param int $productQuantity
     */
    public function setProductStockQuantity(Product $product, Stock $stock, int $productQuantity)
    {
        $productStock = $this->productStockFactory->findOrCreateProductStock($stock, $product);
        $productStock->setProductQuantity($productQuantity);
        $this->em->flush();
    }
}
