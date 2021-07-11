<?php

declare(strict_types=1);

namespace App\Model\Store;

use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;

class ProductStoreFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var \App\Model\Store\ProductStoreRepository
     */
    private ProductStoreRepository $productStoreRepository;

    /**
     * @param \App\Model\Store\ProductStoreRepository $productStoreRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        ProductStoreRepository $productStoreRepository,
        EntityManagerInterface $em
    ) {
        $this->productStoreRepository = $productStoreRepository;
        $this->em = $em;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Store\Store $store
     * @param \App\Model\Store\ProductStoreData $productStoreData
     */
    public function editProductStoreRelation(Product $product, Store $store, ProductStoreData $productStoreData): void
    {
        $productStore = $this->productStoreRepository->findProductStoreByStoreAndProduct($store, $product);
        if (!$productStore) {
            $productStore = new ProductStore($store, $product);
            $this->em->persist($productStore);
        }
        $productStore->edit($productStoreData);

        $this->em->flush();
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
     * @param string $productCatnum
     * @param int $storeId
     * @return \App\Model\Store\ProductStore|null
     */
    public function findProductStoreByProductCatnumAndStoreId(string $productCatnum, int $storeId): ?ProductStore
    {
        return $this->productStoreRepository->findProductStoreByProductCatnumAndStoreId($storeId, $productCatnum);
    }
}
