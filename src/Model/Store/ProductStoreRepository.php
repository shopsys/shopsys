<?php

declare(strict_types=1);

namespace App\Model\Store;

use App\Model\Product\Product;
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
     * @return \App\Model\Store\ProductStoreRepository|\Doctrine\Persistence\ObjectRepository
     */
    public function getProductStoreRepository()
    {
        return $this->em->getRepository(ProductStore::class);
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
     * @param int $storeId
     * @param string $productCatnum
     * @return \App\Model\Store\ProductStore|null
     */
    public function findProductStoreByProductCatnumAndStoreId(int $storeId, string $productCatnum): ?ProductStore
    {
        return $this->getQueryBuilder()
            ->join(Product::class, 'p', JOIN::WITH, 'ps.product = p')
            ->andWhere('ps.store = :storeId')
            ->andWhere('p.catnum = :productCatnum')
            ->setParameter('storeId', $storeId)
            ->setParameter('productCatnum', $productCatnum)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
