<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat\VectorStore;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class VectorStoreFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStoreData $vectorStoreData
     * @return \Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore
     */
    public function create(VectorStoreData $vectorStoreData): VectorStore
    {
        $entityClassName = $this->entityNameResolver->resolve(VectorStore::class);

        return new $entityClassName($vectorStoreData);
    }
}
