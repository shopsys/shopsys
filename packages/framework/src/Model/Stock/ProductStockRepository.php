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

    protected function getEnabledStocksQueryBuilderByDomainId(int $domainId): QueryBuilder
    {
        return $this->getQueryBuilder()
            ->join(Stock::class, 's', Join::WITH, 's.id = ps.stock')
            ->join(StockDomain::class, 'sd', Join::WITH, 's.id = sd.stock AND sd.domainId = :domainId AND sd.isEnabled = TRUE')
            ->setParameter('domainId', $domainId);
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
            ->getResult();
    }

    public function isProductAvailableOnDomain(Product $product, int $domainId): bool
    {
        $queryBuilder = $this->getEnabledStocksQueryBuilderByDomainId($domainId)
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
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return array<int, int>
     */
    public function getGroupedStockQuantitiesByProductsAndDomainIdIndexedByProductId(
        array $products,
        int $domainId,
    ): array {
        if ($products === []) {
            return [];
        }

        $rows = $this->getEnabledStocksQueryBuilderByDomainId($domainId)
            ->select('IDENTITY(ps.product) AS productId, SUM(ps.productQuantity) AS stockQuantity')
            ->andWhere('ps.product IN (:products)')
            ->groupBy('ps.product')
            ->setParameter('products', $products)
            ->getQuery()
            ->getResult();

        $stockQuantities = array_column($rows, 'stockQuantity', 'productId');

        $stockQuantitiesIndexedByProductId = [];

        foreach ($products as $product) {
            $productId = $product->getId();
            $stockQuantitiesIndexedByProductId[$productId] = (int)($stockQuantities[$productId] ?? 0);
        }

        return $stockQuantitiesIndexedByProductId;
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
