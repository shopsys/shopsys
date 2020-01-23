<?php

declare(strict_types=1);


namespace App\Model\Stock;


use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class StockProductRepository
{

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \App\Model\Stock\StockProductRepository|\Doctrine\Common\Persistence\ObjectRepository
     */
    public function getStockProductRepository(){
        return $this->em->getRepository(StockProduct::class);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('sp')
            ->from(StockProduct::class, 'sp')
            ;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Stock\StockProduct[]
     */
    public function getStockProductByProduct(Product $product): array
    {
        return $this->getQueryBuilder()
            ->where('sp.product = :product')
            ->setParameter('product', $product)
            ->getQuery()
            ->execute();
    }
}