<?php

declare(strict_types=1);

namespace App\Model\Store;

use App\Model\Product\Product;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class ProductStoreRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

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
            ->select('ps')
            ->from(ProductStore::class, 'ps');
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getProductStoreQueryBuilderByProduct(Product $product): QueryBuilder
    {
        return $this->getQueryBuilder()
            ->where('ps.product = :product')
            ->setParameter('product', $product);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return \App\Model\Store\ProductStore[]
     */
    public function getProductStoresByProductAndDomainId(Product $product, int $domainId): array
    {
        return $this->getProductStoreQueryBuilderByProduct($product)
            ->join('ps.store', 's')
            ->join('s.domains', 'sd', Join::WITH, 'sd.domainId = :domainId AND sd.isEnabled = TRUE')
            ->setParameter('domainId', $domainId)
            ->orderBy('s.position', 'ASC')
            ->getQuery()
            ->execute();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Store\ProductStore[]
     */
    public function getProductStoresByProduct(Product $product): array
    {
        return $this->getProductStoreQueryBuilderByProduct($product)
            ->join('ps.store', 's')
            ->orderBy('s.position', 'ASC')
            ->getQuery()
            ->execute();
    }

    /**
     * @param \App\Model\Store\Store $store
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Store\ProductStore|null
     */
    public function findProductStoreByStoreAndProduct(Store $store, Product $product): ?ProductStore
    {
        return $this->getProductStoreQueryBuilderByProduct($product)
            ->andWhere('ps.store = :store')
            ->setParameter('store', $store)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int[] $storeIds
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Store\ProductStore[]
     */
    public function getProductStoresByStoresAndProductIndexedByStoreId(array $storeIds, Product $product): array
    {
        /** @var array{productStore: \App\Model\Store\ProductStore, storeId: int} $productStores */
        $productStores = $this->em->createQueryBuilder()
            ->select('ps productStore, IDENTITY(ps.store) storeId')
            ->from(ProductStore::class, 'ps')
            ->where('ps.product = :product')
            ->andWhere('ps.store IN (:storeIds)')
            ->setParameter('product', $product)
            ->setParameter('storeIds', $storeIds)
            ->getQuery()
            ->getResult();

        $productStoresIndexedByStoreId = [];

        foreach ($productStores as $productStore) {
            $productStoresIndexedByStoreId[$productStore['storeId']] = $productStore['productStore'];
        }

        return $productStoresIndexedByStoreId;
    }

    /**
     * @param int $storeId
     */
    public function createProductStoreRelationForStoreId(int $storeId): void
    {
        $this->em->getConnection()->executeStatement(
            'INSERT INTO product_stores (store_id, product_id, product_exposed)
            SELECT :store_id, id, false FROM products;',
            [
                'store_id' => $storeId,
            ],
            [
                'store_id' => Types::INTEGER,
            ],
        );
    }
}
