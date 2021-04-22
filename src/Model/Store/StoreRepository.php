<?php

declare(strict_types=1);

namespace App\Model\Store;

use App\Model\Store\Exception\StoreNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class StoreRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getStoreRepository(): EntityRepository
    {
        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->entityManager->getRepository(Store::class);
        return $repository;
    }

    /**
     * @param int $id
     * @return \App\Model\Store\Store
     */
    public function getById(int $id): Store
    {
        /** @var \App\Model\Store\Store|null $store */
        $store = $this->getStoreRepository()->find($id);

        if (!$store) {
            throw new StoreNotFoundException($id);
        }

        return $store;
    }

    /**
     * @return \App\Model\Store\Store[]
     */
    public function getAll(): array
    {
        return $this->getStoreRepository()->findAll();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryBuilder(): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(Store::class, 's');
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllStoresQueryBuilder(): QueryBuilder
    {
        return $this->getQueryBuilder()->orderBy('s.position', 'ASC');
    }

    /**
     * @param \App\Model\Store\Store $store
     */
    public function changeDefaultStore(Store $store): void
    {
        $this->entityManager->createQueryBuilder()
            ->update(Store::class, 's')
            ->set('s.isDefault', 'FALSE')
            ->getQuery()
            ->execute();

        $store->setDefault();
        $this->entityManager->flush();
    }
}
