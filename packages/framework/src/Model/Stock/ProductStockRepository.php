<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductStockRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('ps')
            ->from(ProductStock::class, 'ps');
    }

    protected function getProductStockQueryBuilderByProduct(Product $product): QueryBuilder
    {
        return $this->getQueryBuilder()
            ->where('ps.product = :product')
            ->setParameter('product', $product);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
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
     * @param int[] $stockIds
     * @return \Shopsys\FrameworkBundle\Model\Stock\ProductStock[]
     */
    public function getProductStocksByStocksAndProductIndexedByStockId(array $stockIds, Product $product): array
    {
        /** @var array{productStock: \Shopsys\FrameworkBundle\Model\Stock\ProductStock, stockId: int} $productStocks */
        $productStocks = $this->em->createQueryBuilder()
            ->select('ps productStock, IDENTITY(ps.stock) stockId')
            ->from(ProductStock::class, 'ps')
            ->where('ps.product = :product')
            ->andWhere('ps.stock IN (:stockIds)')
            ->setParameter('product', $product)
            ->setParameter('stockIds', $stockIds)
            ->getQuery()
            ->getResult();

        $productStocksIndexedByStockId = [];

        foreach ($productStocks as $productStock) {
            $productStocksIndexedByStockId[$productStock['stockId']] = $productStock['productStock'];
        }

        return $productStocksIndexedByStockId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Stock\ProductStock[]
     */
    public function getProductStocksByProduct(Product $product): array
    {
        return $this->getProductStockQueryBuilderByProduct($product)
            ->join('ps.stock', 's')
            ->orderBy('s.position', 'ASC')
            ->getQuery()
            ->execute();
    }

    public function isProductAvailableOnDomain(Product $product, int $domainId): bool
    {
        $queryBuilder = $this->getQueryBuilder()
            ->join(Stock::class, 's', Join::WITH, 's.id = ps.stock')
            ->join(StockDomain::class, 'sd', Join::WITH, 's.id = sd.stock AND sd.domainId = :domainId AND sd.isEnabled = TRUE')
            ->setParameter('domainId', $domainId)
            ->select('CASE WHEN SUM(ps.productQuantity) > 0 THEN TRUE ELSE FALSE END');

        if ($product->isMainVariant()) {
            $queryBuilder
                ->join(Product::class, 'p', Join::WITH, 'ps.product = p AND p.mainVariant = :product')
                ->join('p.domains', 'pdm', Join::WITH, 'pdm.domainId = :domainId AND pdm.calculatedSellingDenied = FALSE');
        } else {
            $queryBuilder->where('ps.product = :product');
        }
        $queryBuilder->setParameter('product', $product);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Stock\ProductStock[]
     */
    public function getProductStocksByProductAndDomainId(Product $product, int $domainId): array
    {
        return $this->getProductStockQueryBuilderByProduct($product)
            ->join(Stock::class, 's', Join::WITH, 's.id = ps.stock')
            ->join(StockDomain::class, 'sd', Join::WITH, 's.id = sd.stock AND sd.domainId = :domainId AND sd.isEnabled = TRUE')
            ->setParameter('domainId', $domainId)
            ->getQuery()
            ->execute();
    }

    public function createProductStockRelationForStockId(int $stockId): void
    {
        $this->em->getConnection()->executeStatement(
            'INSERT INTO product_stocks (stock_id, product_id, product_quantity)
            SELECT :stock_id, id, 0 FROM products;',
            [
                'stock_id' => $stockId,
            ],
            [
                'stock_id' => Types::INTEGER,
            ],
        );
    }
}
