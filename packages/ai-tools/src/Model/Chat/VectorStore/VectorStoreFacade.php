<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat\VectorStore;

use Doctrine\ORM\EntityManagerInterface;

class VectorStoreFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreRepository $vectorStoreRepository
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreFactory $vectorStoreFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly VectorStoreRepository $vectorStoreRepository,
        protected readonly VectorStoreFactory $vectorStoreFactory,
    ) {
    }

    /**
     * @param int $id
     * @return \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore
     */
    public function getById(int $id): VectorStore
    {
        return $this->vectorStoreRepository->getById($id);
    }

    /**
     * @return \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore[]
     */
    public function findAll(): array
    {
        return $this->vectorStoreRepository->findAll();
    }

    /**
     * @param string $externalId
     * @return \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore|null
     */
    public function findByExternalId(string $externalId): ?VectorStore
    {
        return $this->vectorStoreRepository->findByExternalId($externalId);
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreData $vectorStoreData
     * @return \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore
     */
    public function create(VectorStoreData $vectorStoreData): VectorStore
    {
        $vectorStore = $this->vectorStoreFactory->create($vectorStoreData);
        $this->em->persist($vectorStore);
        $this->em->flush();

        return $vectorStore;
    }

    /**
     * @param int $id
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreData $vectorStoreData
     * @return \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore
     */
    public function edit(int $id, VectorStoreData $vectorStoreData): VectorStore
    {
        $vectorStore = $this->getById($id);
        $vectorStore->edit($vectorStoreData);
        $this->em->flush();

        return $vectorStore;
    }

    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        $vectorStore = $this->getById($id);
        $this->em->remove($vectorStore);
        $this->em->flush();
    }
}
