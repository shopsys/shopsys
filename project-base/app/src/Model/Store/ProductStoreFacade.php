<?php

declare(strict_types=1);

namespace App\Model\Store;

use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;

class ProductStoreFacade
{
    /**
     * @param \App\Model\Store\ProductStoreRepository $productStoreRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        private ProductStoreRepository $productStoreRepository,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Store\Store[] $storesIndexedById
     * @param \App\Model\Store\ProductStoreData[] $productStoreDataItems
     */
    public function editProductStoreRelations(
        Product $product,
        array $storesIndexedById,
        array $productStoreDataItems,
    ): void {
        $productStoresIndexedByStoreId = $this->productStoreRepository->getProductStoresByStoresAndProductIndexedByStoreId(
            array_keys($storesIndexedById),
            $product,
        );

        foreach ($storesIndexedById as $storeId => $store) {
            $filteredProductStoreDataItem = array_filter($productStoreDataItems, fn ($productStoreDataItem) => $productStoreDataItem->storeId === $storeId);
            $productStoreData = array_pop($filteredProductStoreDataItem);

            $productStore = $productStoresIndexedByStoreId[$storeId] ?? $this->createProductStore($product, $store);
            $productStore->edit($productStoreData);
        }

        $this->em->flush();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Store\Store $store
     * @return \App\Model\Store\ProductStore
     */
    public function createProductStore(Product $product, Store $store): ProductStore
    {
        $productStore = new ProductStore($store, $product);
        $this->em->persist($productStore);
        $this->em->flush();

        return $productStore;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return \App\Model\Store\ProductStore[]
     */
    public function getProductStoresByProductAndDomainId(Product $product, int $domainId): array
    {
        return $this->productStoreRepository->getProductStoresByProductAndDomainId($product, $domainId);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Store\ProductStore[]
     */
    public function getProductStoresByProduct(Product $product): array
    {
        return $this->productStoreRepository->getProductStoresByProduct($product);
    }

    /**
     * @param int $storeId
     */
    public function createProductStoreRelationForStoreId(int $storeId): void
    {
        $this->productStoreRepository->createProductStoreRelationForStoreId($storeId);
    }
}
