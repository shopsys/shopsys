<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;

class StoreFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreRepository $storeRepository
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreFactory $storeFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     */
    public function __construct(
        protected readonly StoreRepository $storeRepository,
        protected readonly StoreFactory $storeFactory,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ImageFacade $imageFacade,
        protected readonly EntityManagerInterface $em,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    public function getAllStores(): array
    {
        return $this->storeRepository->getAll();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreData $storeData
     * @return \Shopsys\FrameworkBundle\Model\Store\Store
     */
    public function create(StoreData $storeData): Store
    {
        $store = $this->storeFactory->create($storeData);
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
}
