<?php

declare(strict_types=1);

namespace App\Model\Product\Series;

use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class ProductSeriesProductRepository
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
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('psp')
            ->from(ProductSeriesProduct::class, 'psp');
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\Series\ProductSeriesProduct[]
     */
    public function findByProduct(Product $product): array
    {
        return $this->getQueryBuilder()
            ->where('psp.product = :product')
            ->setParameter('product', $product)
            ->getQuery()
            ->execute();
    }

    /**
     * @param \App\Model\Product\Series\ProductSeries $productSeries
     * @return \App\Model\Product\Series\ProductSeriesProduct[]
     */
    public function findByProductSeries(ProductSeries $productSeries): array
    {
        return $this->getQueryBuilder()
            ->where('psp.productSeries = :productSeries')
            ->setParameter('productSeries', $productSeries)
            ->getQuery()
            ->execute();
    }

    /**
     * @param \App\Model\Product\Series\ProductSeries $productSeries
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\Series\ProductSeriesProduct|null
     */
    public function findByProductSeriesAndProduct(ProductSeries $productSeries, Product $product): ?ProductSeriesProduct
    {
        return $this->getQueryBuilder()
            ->where('psp.product = :product')
            ->andWhere('psp.productSeries = :productSeries')
            ->setParameter('product', $product)
            ->setParameter('productSeries', $productSeries)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
