<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
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
    protected function getAllStoresQueryBuilder(): QueryBuilder
    {
        return $this->getQueryBuilder()->orderBy('s.position, s.id', 'ASC');
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Store\StoresFilterOptions|null $storesFilterOptions
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getStoresByDomainIdQueryBuilder(
        int $domainId,
        ?StoresFilterOptions $storesFilterOptions = null,
    ): QueryBuilder {
        $queryBuilder = $this->getAllStoresQueryBuilder()
            ->andWhere('s.domainId = :domainId')
            ->setParameter('domainId', $domainId);

        if ($storesFilterOptions !== null) {
            if ($storesFilterOptions->getSearchText() !== null) {
                $queryBuilder
                    ->andWhere('(normalized(s.city) LIKE normalized(:searchText) OR normalized(s.postcode) LIKE normalized(:searchText))')
                    ->setParameter('searchText', DatabaseSearching::getFullTextLikeSearchString($storesFilterOptions->getSearchText()))
                ;
            }
        }

        return $queryBuilder;
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
     * @param \Shopsys\FrameworkBundle\Model\Store\StoresFilterOptions|null $storesFilterOptions
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    public function getStoresByDomainId(
        int $domainId,
        ?int $limit = null,
        ?int $offset = null,
        ?StoresFilterOptions $storesFilterOptions = null,
    ): array {
        $queryBuilder = $this->getStoresByDomainIdQueryBuilder($domainId, $storesFilterOptions);

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
     * @param \Shopsys\FrameworkBundle\Model\Store\StoresFilterOptions|null $storesFilterOptions
     * @return int
     */
    public function getStoresCountByDomainId(int $domainId, ?StoresFilterOptions $storesFilterOptions): int
    {
        $queryBuilder = $this->getStoresByDomainIdQueryBuilder($domainId, $storesFilterOptions)
            ->resetDQLPart('orderBy')
            ->select('COUNT(s)');

        return (int)$queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string $uuid
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Store\Store
     */
    public function getByUuidAndDomainId(string $uuid, int $domainId): Store
    {
        $store = $this->getStoresByDomainIdQueryBuilder($domainId)
            ->andWhere('s.uuid = :uuid')
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
    public function getByIdAndDomainId(int $id, int $domainId): Store
    {
        $store = $this->getStoresByDomainIdQueryBuilder($domainId)
            ->andWhere('s.id = :id')
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
