<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Store;

class StoreFacade
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Store\StoreRepository $storeRepository
     */
    public function __construct(
        protected readonly StoreRepository $storeRepository,
    ) {
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrontendApiBundle\Model\Store\StoresFilterOptions $storesFilterOptions
     * @return int
     */
    public function getFilteredStoresCount(int $domainId, StoresFilterOptions $storesFilterOptions): int
    {
        return $this->storeRepository->getFilteredStoresCount($domainId, $storesFilterOptions);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrontendApiBundle\Model\Store\StoresFilterOptions $storesFilterOptions
     * @param int|null $limit
     * @param int|null $offset
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
