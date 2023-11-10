<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Store\Exception\StoreByUuidNotFoundException;
use Shopsys\FrameworkBundle\Model\Store\Exception\StoreNotFoundException;

class StoreRepository
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
    protected function getStoreRepository(): EntityRepository
    {
        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->em->getRepository(Store::class);

        return $repository;
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Store\Store
     */
    public function getById(int $id): Store
    {
        /** @var \Shopsys\FrameworkBundle\Model\Store\Store|null $store */
        $store = $this->getStoreRepository()->find($id);

        if (!$store) {
            throw new StoreNotFoundException($id);
        }

        return $store;
    }

    /**
     * @param int[] $storeIds
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    public function getStoresByIdsIndexedById(array $storeIds): array
    {
        return $this->getStoreRepository()
            ->createQueryBuilder('s', 's.id')
            ->where('s.id IN (:storeIds)')
            ->setParameter('storeIds', $storeIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    public function getAll(): array
    {
        return $this->getStoreRepository()->findAll();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('s')
            ->from(Store::class, 's');
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllStoresQueryBuilder(): QueryBuilder
    {
        return $this->getQueryBuilder()->orderBy('s.position, s.id', 'ASC');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     */
    public function changeDefaultStore(Store $store): void
    {
        $this->em->createQueryBuilder()
            ->update(Store::class, 's')
            ->set('s.isDefault', 'FALSE')
            ->getQuery()
            ->execute();

        $store->setDefault();
        $this->em->flush();
    }

    /**
     * @param int $domainId
     * @param int|null $limit
     * @param int|null $offset
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    public function getStoresEnabledOnDomain(int $domainId, ?int $limit = null, ?int $offset = null): array
    {
        $queryBuilder = $this->getAllStoresQueryBuilder()
            ->join(StoreDomain::class, 'sd', Join::WITH, 's.id = sd.store AND sd.isEnabled = TRUE AND sd.domainId = :domainId')
            ->setParameter('domainId', $domainId);

        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit);
        }

        if ($offset !== null) {
            $queryBuilder->setFirstResult($offset);
        }

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param string $externalId
     * @return \Shopsys\FrameworkBundle\Model\Store\Store|null
     */
    public function findStoreByExternalId(string $externalId): ?Store
    {
        return $this->getStoreRepository()->findOneBy(['externalId' => $externalId]);
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
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Store\Store
     */
    public function getByUuidEnabledOnDomain(string $uuid, int $domainId): Store
    {
        $store = $this->getQueryBuilder()
            ->join(StoreDomain::class, 'sd', Join::WITH, 's.id = sd.store AND sd.isEnabled = TRUE AND sd.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->where('s.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult();

        if ($store === null) {
            throw new StoreByUuidNotFoundException(sprintf('Store with UUID "%s" does not exist.', $uuid));
        }

        return $store;
    }

    /**
     * @param int $id
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Store\Store
     */
    public function getByIdEnabledOnDomain(int $id, int $domainId): Store
    {
        $store = $this->getQueryBuilder()
            ->join(StoreDomain::class, 'sd', Join::WITH, 's.id = sd.store AND sd.isEnabled = TRUE AND sd.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if ($store === null) {
            throw new StoreNotFoundException($id);
        }

        return $store;
    }

    /**
     * @param int[] $storeIds
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    public function getStoresByIds(array $storeIds): array
    {
        $queryBuilder = $this->getQueryBuilder()
            ->select('s')
            ->where('s.id IN (:storeIds)')
            ->setParameter('storeIds', $storeIds);

        return $queryBuilder->getQuery()->execute();
    }
}
