<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
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
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getProductStockQueryBuilderByProduct(Product $product): QueryBuilder
    {
        return $this->getQueryBuilder()
            ->where('sp.product = :product')
            ->setParameter('product', $product);
    }

    /**
     * @param \App\Model\Stock\Stock $stock
     * @param \App\Model\Product\Product $product
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return \App\Model\Stock\ProductStock|null
     */
    public function findProductStockByStockAndProduct(Stock $stock, Product $product): ?ProductStock
    {
        return $this->getProductStockQueryBuilderByProduct($product)
            ->andWhere('sp.stock = :stock')
            ->setParameter('stock', $stock)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $stockExternalId
     * @param string $productCatnum
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return \App\Model\Stock\ProductStock|null
     */
    public function findProductStockByStockExternalIdAndProductCatnum(string $stockExternalId, string $productCatnum): ?ProductStock
    {
        return $this->getQueryBuilder()
            ->join(Product::class, 'p', JOIN::WITH, 'sp.product = p')
            ->join(Stock::class, 's', JOIN::WITH, 'sp.stock = s')
            ->andWhere('s.externalId = :stockExternalId')
            ->andWhere('p.catnum = :productCatnum')
            ->setParameter('stockExternalId', $stockExternalId)
            ->setParameter('productCatnum', $productCatnum)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Stock\ProductStock[]
     */
    public function getProductStocksByProduct(Product $product): array
    {
        return $this->getProductStockQueryBuilderByProduct($product)
            ->getQuery()
            ->execute();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return \App\Model\Stock\ProductStock[]
     */
    public function getProductStocksExcludeCentralStockByProductAndDomainId(Product $product, int $domainId): array
    {
        return $this->getProductStockQueryBuilderByProduct($product)
            ->join(Stock::class, 's', Join::WITH, 's.id = sp.stock')
            ->andWhere('s.centralStock = false')
            ->andWhere('s.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->getQuery()
            ->execute();
    }

    /**
     * @return \App\Model\Stock\ProductStock[]
     */
    public function findFutureProductStockAfterNowDate(): array
    {
        return $this->getQueryBuilder()
            ->where('sp.dateOfStorage IS NOT NULL')
            ->andWhere('sp.dateOfStorage < :nowDate')
            ->setParameter('nowDate', new \DateTime())
            ->getQuery()
            ->execute();
    }
}
