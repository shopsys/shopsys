<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat\VectorStore;

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
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreData $vectorStoreData
     * @return \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore
     */
    public function create(VectorStoreData $vectorStoreData): VectorStore
    {
        $entityClassName = $this->entityNameResolver->resolve(VectorStore::class);

        return new $entityClassName($vectorStoreData);
    }
}
