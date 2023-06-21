<?php

declare(strict_types=1);

namespace App\Model\Store;

class StoreFactory
{
    /**
     * @param \App\Model\Store\StoreData $storeData
     * @return \App\Model\Store\Store
     */
    public function create(StoreData $storeData): Store
    {
        return new Store($storeData);
    }
}
