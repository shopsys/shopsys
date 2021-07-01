<?php

declare(strict_types=1);

namespace App\Model\Store;

use App\Model\Store\Exception\StoreByUuidNotFoundException;
use App\Model\Store\Exception\StoreNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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

    /**
     * @param int $domainId
     * @return \App\Model\Store\Store[]
     */
    public function getStoresEnabledOnDomain(int $domainId): array
    {
        return $this->getQueryBuilder()
            ->join(StoreDomain::class, 'sd', Join::WITH, 's.id = sd.store AND sd.isEnabled = TRUE AND sd.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->getQuery()
            ->execute();
    }

    /**
     * @param string $externalId
     * @return \App\Model\Store\Store|null
     */
    public function findStoreByExternalId(string $externalId): ?Store
    {
        return $this->getStoreRepository()->findOneBy(['externalId' => $externalId]);
    }

    /**
     * @param int $domainId
     * @param int $limit
     * @param int $offset
     * @return \App\Model\Store\Store[]
     */
    public function getStoresListEnabledOnDomain(int $domainId, int $limit, int $offset): array
    {
        return $this->getQueryBuilder()
            ->join(StoreDomain::class, 'sd', Join::WITH, 's.id = sd.store AND sd.isEnabled = TRUE AND sd.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getStoresCountEnabledOnDomain(int $domainId): int
    {
        $queryBuilder = $this->getQueryBuilder()
            ->select('COUNT(s)')
            ->join(StoreDomain::class, 'sd', Join::WITH, 's.id = sd.store AND sd.isEnabled = TRUE AND sd.domainId = :domainId')
            ->setParameter('domainId', $domainId);

        return (int)$queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string $uuid
     * @return \App\Model\Store\Store
     */
    public function getOneByUuid(string $uuid): Store
    {
        $store = $this->getStoreRepository()->findOneBy(['uuid' => $uuid], ['position' => 'ASC', 'id' => 'ASC']);

        if ($store === null) {
            throw new StoreByUuidNotFoundException(sprintf('Store with UUID "%s" does not exist.', $uuid));
        }

        return $store;
    }
}
