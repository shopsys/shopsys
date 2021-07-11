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
        return $this->createInstance();
    }
}
