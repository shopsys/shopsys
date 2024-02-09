<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursFactory;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeFactory;

class StoreFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreRepository $storeRepository
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreFactory $storeFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursFactory $openingHoursFactory
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory $openingHoursDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeFactory $openingHoursRangeFactory
     */
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
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreData $storeData
     * @return \Shopsys\FrameworkBundle\Model\Store\Store
     */
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

        return $store;
    }

    /**
     * @param int $id
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreData $storeData
     * @return \Shopsys\FrameworkBundle\Model\Store\Store
     */
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

        return $store;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     */
    protected function createFriendlyUrl(Store $store): void
    {
        $this->friendlyUrlFacade->createFriendlyUrlForDomain(
            StoreFriendlyUrlProvider::ROUTE_NAME,
            $store->getId(),
            $store->getName(),
            $store->getDomainId(),
        );
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Store\Store
     */
    public function getById(int $id): Store
    {
        return $this->storeRepository->getById($id);
    }

    /**
     * @param int $storeId
     */
    public function delete(int $storeId): void
    {
        $store = $this->getById($storeId);
        $this->em->remove($store);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     */
    public function changeDefaultStore(Store $store): void
    {
        $this->storeRepository->changeDefaultStore($store);
    }

    /**
     * @param string $externalId
     * @return \Shopsys\FrameworkBundle\Model\Store\Store|null
     */
    public function findStoreByExternalId(string $externalId): ?Store
    {
        return $this->storeRepository->findStoreByExternalId($externalId);
    }

    /**
     * @param int $domainId
     * @param int|null $limit
     * @param int|null $offset
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    public function getStoresByDomainId(int $domainId, ?int $limit = null, ?int $offset = null): array
    {
        return $this->storeRepository->getStoresByDomainId($domainId, $limit, $offset);
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getStoresCountByDomainId(int $domainId): int
    {
        return $this->storeRepository->getStoresCountByDomainId($domainId);
    }

    /**
     * @param string $uuid
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Store\Store
     */
    public function getByUuidAndDomainId(string $uuid, int $domainId): Store
    {
        return $this->storeRepository->getByUuidAndDomainId($uuid, $domainId);
    }

    /**
     * @param int $id
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Store\Store
     */
    public function getByIdAndDomainId(int $id, int $domainId): Store
    {
        return $this->storeRepository->getByIdAndDomainId($id, $domainId);
    }

    /**
     * @param int[] $storeIds
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    public function getStoresByIds(array $storeIds): array
    {
        return $this->storeRepository->getStoresByIds($storeIds);
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getStoresByDomainIdQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->storeRepository->getStoresByDomainIdQueryBuilder($domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[] $openingHoursDataArray
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours[]
     */
    protected function createFullWeekOpeningHours(array $openingHoursDataArray, Store $store): array
    {
        $openingHours = [];
        $daysCovered = [];

        foreach ($openingHoursDataArray as $openingHoursData) {
            $openingHour = $this->openingHoursFactory->create($openingHoursData);
            $openingHour->setOpeningHoursRanges($this->openingHoursRangeFactory->createOpeningHoursRanges($openingHour, $openingHoursData->openingHoursRanges));
            $openingHours[] = $openingHour;
            $daysCovered[] = $openingHoursData->dayOfWeek;
        }

        $daysOfWeek = range(1, 7);
        $missingDays = array_diff($daysOfWeek, $daysCovered);

        foreach ($missingDays as $missingDay) {
            $openingHoursData = $this->openingHoursDataFactory->createForDayOfWeek($missingDay);
            $openingHours[] = $this->openingHoursFactory->create($openingHoursData);
        }

        return array_map(
            static function (OpeningHours $openingHours) use ($store): OpeningHours {
                $openingHours->setStore($store);

                return $openingHours;
            },
            $openingHours,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreData $storeData
     */
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
