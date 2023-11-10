<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

class StoreFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreData $storeData
     * @return \Shopsys\FrameworkBundle\Model\Store\Store
     */
    public function create(StoreData $storeData): Store
    {
        return new Store($storeData);
    }
}
