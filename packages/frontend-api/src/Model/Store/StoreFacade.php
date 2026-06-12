<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Store;

class StoreFacade
{
    public function __construct(
        protected readonly StoreRepository $storeRepository,
    ) {
    }

    public function getFilteredStoresCount(int $domainId, StoresFilterOptions $storesFilterOptions): int
    {
        return $this->storeRepository->getFilteredStoresCount($domainId, $storesFilterOptions);
    }

    /**
     * @return array{latitude: string, longitude: string}|null
     */
    public function findStoreCoordinatesBySearchText(int $domainId, string $searchText): ?array
    {
        return $this->storeRepository->findStoreCoordinatesBySearchText($domainId, $searchText);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    public function getFilteredStores(
        int $domainId,
        StoresFilterOptions $storesFilterOptions,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->storeRepository->getFilteredQueryBuilder($domainId, $storesFilterOptions, $limit, $offset);
    }
}
