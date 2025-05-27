<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat\VectorStore;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VectorStoreRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(VectorStore::class);
    }

    /**
     * @param int $id
     * @return \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore
     */
    public function getById(int $id): VectorStore
    {
        $vectorStore = $this->getRepository()->find($id);

        if ($vectorStore === null) {
            throw new NotFoundHttpException(sprintf('VectorStore with id `%d` does not exist.', $id));
        }

        return $vectorStore;
    }

    /**
     * @return \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore[]
     */
    public function findAll(): array
    {
        return $this->getRepository()->findAll();
    }

    /**
     * @param string $externalId
     * @return \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStore|null
     */
    public function findByExternalId(string $externalId): ?VectorStore
    {
        return $this->getRepository()->findOneBy(['externalId' => $externalId]);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllQueryBuilder(): QueryBuilder
    {
        return $this->getRepository()->createQueryBuilder('vs');
    }
}
