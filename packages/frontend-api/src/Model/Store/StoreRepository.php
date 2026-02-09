<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Store;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Model\Store\Store;

class StoreRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(Store::class, 's');
    }

    public function getFilteredStoresCount(int $domainId, StoresFilterOptions $storesFilterOptions): int
    {
        $queryBuilder = $this->getBasicFilteredQueryBuilder($domainId, $storesFilterOptions)
            ->select('COUNT(s)');

        return (int)$queryBuilder->getQuery()->getSingleScalarResult();
    }

    protected function getBasicFilteredQueryBuilder(
        int $domainId,
        StoresFilterOptions $storesFilterOptions,
    ): QueryBuilder {
        $queryBuilder = $this->getQueryBuilder()
            ->andWhere('s.domainId = :domainId')
            ->setParameter('domainId', $domainId);

        if ($storesFilterOptions->getSearchText() !== null) {
            $queryBuilder
                ->andWhere('(normalized(s.city) LIKE normalized(:searchText) OR normalized(s.postcode) LIKE normalized(:searchText))')
                ->setParameter('searchText', $this->databaseSearchingHelper->getFullTextLikeSearchString($storesFilterOptions->getSearchText()));
        }

        return $queryBuilder;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    public function getFilteredQueryBuilder(
        int $domainId,
        StoresFilterOptions $storesFilterOptions,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        $queryBuilder = $this->getBasicFilteredQueryBuilder($domainId, $storesFilterOptions);
        $queryBuilder->orderBy('s.position, s.id', 'ASC');

        if ($storesFilterOptions->getCoordinates() !== null) {
            $coordinates = $storesFilterOptions->getCoordinates();

            $queryBuilder->addSelect('DISTANCE(s.latitude, s.longitude, :latitude, :longitude) AS distance')
                ->setParameter('latitude', (float)$coordinates['latitude'])
                ->setParameter('longitude', (float)$coordinates['longitude'])
                ->orderBy('distance', 'ASC');
        }

        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit);
        }

        if ($offset !== null) {
            $queryBuilder->setFirstResult($offset);
        }

        $results = $queryBuilder->getQuery()->getResult();

        if ($storesFilterOptions->getCoordinates() === null) {
            return $results;
        }

        $stores = [];

        foreach ($results as $storeArray) {
            /** @var \Shopsys\FrameworkBundle\Model\Store\Store $store */
            $store = $storeArray[0];

            /** @var string|null $distance */
            $distance = $storeArray['distance'] ?? null;

            if ($distance !== null) {
                $store->setDistance((int)$distance);
            }

            $stores[$store->getId()] = $store;
        }

        return $stores;
    }
}
