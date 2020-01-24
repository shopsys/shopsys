<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class ProductStockRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \App\Model\Stock\ProductStockRepository|\Doctrine\Common\Persistence\ObjectRepository
     */
    public function getProductStockRepository()
    {
        return $this->em->getRepository(ProductStock::class);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('sp')
            ->from(ProductStock::class, 'sp');
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Stock\ProductStock[]
     */
    public function getProductStockByProduct(Product $product): array
    {
        return $this->getQueryBuilder()
            ->where('sp.product = :product')
            ->setParameter('product', $product)
            ->getQuery()
            ->execute();
    }
}
