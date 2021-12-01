<?php

declare(strict_types=1);

namespace App\Model\Store;

class ProductStoreDataFactory
{
    /**
     * @return \App\Model\Store\ProductStoreData
     */
    private function createInstance(): ProductStoreData
    {
        return new ProductStoreData();
    }

    /**
     * @param \App\Model\Store\Store $store
     * @return \App\Model\Store\ProductStoreData
     */
    public function createFromStore(Store $store): ProductStoreData
    {
        $productStoreData = $this->createInstance();

        $productStoreData->name = $store->getName();
        $productStoreData->storeId = $store->getId();

        return $productStoreData;
    }

    /**
     * @param \App\Model\Store\ProductStore $productStore
     * @return \App\Model\Store\ProductStoreData
     */
    public function createFromProductStore(ProductStore $productStore): ProductStoreData
    {
        $productStoreData = $this->createInstance();

        $productStoreData->productExposed = $productStore->isProductExposed();
        $productStoreData->name = $productStore->getStore()->getName();

        $productStoreData->storeId = $productStore->getStore()->getId();

        return $productStoreData;
    }
}
