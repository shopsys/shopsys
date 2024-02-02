<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class StoreFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreData $storeData
     * @return \Shopsys\FrameworkBundle\Model\Store\Store
     */
    public function create(StoreData $storeData): Store
    {
        $entityClassName = $this->entityNameResolver->resolve(Store::class);

        /** @var \Shopsys\FrameworkBundle\Model\Store\Store $store */
        $store = new $entityClassName($storeData);

        return $store;
    }
}
