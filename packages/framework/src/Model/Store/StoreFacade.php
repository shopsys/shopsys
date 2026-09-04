<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursFactory;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeFactory;

class StoreFacade
{
    public function __construct(
        protected readonly StoreRepository $storeRepository,
        protected readonly StoreFactory $storeFactory,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ImageFacade $imageFacade,
        protected readonly EntityManagerInterface $em,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        protected readonly OpeningHoursFactory $openingHoursFactory,
        protected readonly OpeningHoursDataFactory $openingHoursDataFactory,
        protected readonly OpeningHoursRangeFactory $openingHoursRangeFactory,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    public function create(StoreData $storeData): Store
    {
        $store = $this->storeFactory->create($storeData);
        $store->setOpeningHours(
            $this->createFullWeekOpeningHours($storeData->openingHours, $store),
        );
        $this->em->persist($store);
        $this->em->flush();

        $this->createFriendlyUrl($store);

        $this->imageFacade->manageImages($store, $storeData->image);
        $this->productRecalculationDispatcher->dispatchAllProducts();

        $this->cleanStorefrontStoresQueriesCache();

        return $store;
    }

    public function edit(int $id, StoreData $storeData): Store
    {
        $store = $this->getById($id);
        $store->edit($storeData);
        $this->refreshStoreOpeningHours($store, $storeData);
        $this->friendlyUrlFacade->saveUrlListFormData(StoreFriendlyUrlProvider::ROUTE_NAME, $store->getId(), $storeData->urls);
        $this->em->flush();

        $this->imageFacade->manageImages($store, $storeData->image);

        $this->createFriendlyUrl($store);
        $this->productRecalculationDispatcher->dispatchAllProducts();

        $this->cleanStorefrontStoresQueriesCache();

        return $store;
    }

    protected function createFriendlyUrl(Store $store): void
    {
        $this->friendlyUrlFacade->createFriendlyUrlForDomain(
            StoreFriendlyUrlProvider::ROUTE_NAME,
            $store->getId(),
            $store->getName(),
            $store->getDomainId(),
        );
    }

    public function getById(int $id): Store
    {
        return $this->storeRepository->getById($id);
    }

    public function delete(int $storeId): void
    {
        $store = $this->getById($storeId);
        $this->em->remove($store);
        $this->em->flush();

        $this->cleanStorefrontStoresQueriesCache();
    }

    public function changeDefaultStore(Store $store): void
    {
        $this->storeRepository->changeDefaultStore($store);
    }

    protected function cleanStorefrontStoresQueriesCache(): void
    {
        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::STORES_QUERY_KEY_PART);
        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::MAP_STORES_QUERY_KEY_PART);
    }

    public function findStoreByExternalId(string $externalId): ?Store
    {
        return $this->storeRepository->findStoreByExternalId($externalId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    public function getStoresByDomainId(int $domainId, ?int $limit = null, ?int $offset = null): array
    {
        return $this->storeRepository->getStoresByDomainId($domainId, $limit, $offset);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    public function getStoresByDomainIdWithEagerLoadedOpeningHours(int $domainId): array
    {
        return $this->storeRepository->getStoresByDomainIdWithEagerLoadedOpeningHours($domainId);
    }

    public function findByUuidAndDomainId(string $uuid, int $domainId): ?Store
    {
        return $this->storeRepository->findByUuidAndDomainId($uuid, $domainId);
    }

    public function getByUuidAndDomainId(string $uuid, int $domainId): Store
    {
        return $this->storeRepository->getByUuidAndDomainId($uuid, $domainId);
    }

    public function getByIdAndDomainId(int $id, int $domainId): Store
    {
        return $this->storeRepository->getByIdAndDomainId($id, $domainId);
    }

    public function getStoresByDomainIdQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->storeRepository->getStoresByDomainIdQueryBuilder($domainId);
    }

    /**
     * @return int[]
     */
    public function getStoreCountsByDomainIdIndexedByStockId(int $domainId): array
    {
        return $this->storeRepository->getStoreCountsByDomainIdIndexedByStockId($domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[] $openingHoursDataArray
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours[]
     */
    protected function createFullWeekOpeningHours(array $openingHoursDataArray, Store $store): array
    {
        $openingHours = [];
        $daysCovered = [];

        foreach ($openingHoursDataArray as $openingHoursData) {
            $openingHour = $this->openingHoursFactory->createWithStore($openingHoursData, $store);
            $openingHour->setOpeningHoursRanges($this->openingHoursRangeFactory->createOpeningHoursRanges($openingHour, $openingHoursData->openingHoursRanges));
            $openingHours[] = $openingHour;
            $daysCovered[] = $openingHoursData->dayOfWeek;
        }

        $daysOfWeek = range(1, 7);
        $missingDays = array_diff($daysOfWeek, $daysCovered);

        foreach ($missingDays as $missingDay) {
            $openingHoursData = $this->openingHoursDataFactory->createForDayOfWeek($missingDay);
            $openingHour = $this->openingHoursFactory->createWithStore($openingHoursData, $store);
            $openingHour->setOpeningHoursRanges($this->openingHoursRangeFactory->createOpeningHoursRanges($openingHour, $openingHoursData->openingHoursRanges));
            $openingHours[] = $openingHour;
        }

        return $openingHours;
    }

    protected function refreshStoreOpeningHours(Store $store, StoreData $storeData): void
    {
        foreach ($store->getOpeningHours() as $openingHours) {
            $this->em->remove($openingHours);
        }
        $this->em->flush();
        $store->setOpeningHours(
            $this->createFullWeekOpeningHours($storeData->openingHours, $store),
        );
    }
}
