<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat\VectorStore;

class VectorStoreDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStoreData
     */
    protected function createInstance(): VectorStoreData
    {
        return new VectorStoreData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStoreData
     */
    public function create(): VectorStoreData
    {
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @return \Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStoreData
     */
    public function createFromVectorStore(VectorStore $vectorStore): VectorStoreData
    {
        $vectorStoreData = $this->createInstance();
        $vectorStoreData->uuid = $vectorStore->getUuid();
        $vectorStoreData->name = $vectorStore->getName();
        $vectorStoreData->description = $vectorStore->getDescription();
        $vectorStoreData->externalId = $vectorStore->getExternalId();
        $vectorStoreData->dataStructure = $vectorStore->getDataStructure();

        return $vectorStoreData;
    }
}
