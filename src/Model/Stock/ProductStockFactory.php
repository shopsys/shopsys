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
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param \App\Model\Stock\Stock $stock
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Stock\ProductStock
     */
    public function create(Stock $stock, Product $product): ProductStock
    {
        $stockProduct = new ProductStock($stock, $product);
        $this->em->persist($stockProduct);
        $this->em->flush();
        return $stockProduct;
    }
}
