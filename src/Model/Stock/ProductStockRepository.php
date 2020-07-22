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
            ->select('ps')
            ->from(ProductStock::class, 'ps');
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getProductStockQueryBuilderByProduct(Product $product): QueryBuilder
    {
        return $this->getQueryBuilder()
            ->where('ps.product = :product')
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
            ->andWhere('ps.stock = :stock')
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
            ->join(Product::class, 'p', JOIN::WITH, 'ps.product = p')
            ->join(Stock::class, 's', JOIN::WITH, 'ps.stock = s')
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
     * @return bool
     */
    public function isProductAvailableOnDomain(Product $product, int $domainId): bool
    {
        $queryBuilder = $this->getQueryBuilder()
            ->join(Stock::class, 's', Join::WITH, 's.id = ps.stock AND s.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->select('CASE WHEN SUM(ps.productQuantity) > 0 THEN TRUE ELSE FALSE END');

        if ($product->isMainVariant()) {
            $queryBuilder->join(Product::class, 'p', Join::WITH, 'ps.product = p AND p.mainVariant = :product');
        } else {
            $queryBuilder->where('ps.product = :product');
        }
        $queryBuilder->setParameter('product', $product);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return \App\Model\Stock\ProductStock[]
     */
    public function getProductStocksExcludeCentralStockByProductAndDomainId(Product $product, int $domainId): array
    {
        return $this->getProductStockQueryBuilderByProduct($product)
            ->join(Stock::class, 's', Join::WITH, 's.id = ps.stock')
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
            ->where('ps.dateOfStorage IS NOT NULL')
            ->andWhere('ps.dateOfStorage < :nowDate')
            ->setParameter('nowDate', new \DateTime())
            ->getQuery()
            ->execute();
    }
}
