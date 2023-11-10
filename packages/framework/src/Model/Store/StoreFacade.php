<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class StoreFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreRepository $storeRepository
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreFactory $storeFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     */
    public function __construct(
        private readonly StoreRepository $storeRepository,
        private readonly StoreFactory $storeFactory,
        private readonly FriendlyUrlFacade $friendlyUrlFacade,
        private readonly ImageFacade $imageFacade,
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository $productRepository,
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
        $this->productRepository->markAllProductsForExport();

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
        $this->productRepository->markAllProductsForExport();

        return $store;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     */
    protected function createFriendlyUrl(Store $store): void
    {
        foreach ($store->getEnabledDomains() as $storeDomain) {
            $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                StoreFriendlyUrlProvider::ROUTE_NAME,
                $store->getId(),
                $store->getName(),
                $storeDomain->getDomainId(),
            );
        }
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
     * @param int[] $storeIds
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    public function getStoresByIdsIndexedById(array $storeIds): array
    {
        return $this->storeRepository->getStoresByIdsIndexedById($storeIds);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllStoresQueryBuilder(): QueryBuilder
    {
        return $this->storeRepository->getAllStoresQueryBuilder();
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
    public function getStoresListEnabledOnDomain(int $domainId, ?int $limit = null, ?int $offset = null): array
    {
        return $this->storeRepository->getStoresEnabledOnDomain($domainId, $limit, $offset);
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getStoresCountEnabledOnDomain(int $domainId): int
    {
        return $this->storeRepository->getStoresCountEnabledOnDomain($domainId);
    }

    /**
     * @param string $uuid
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Store\Store
     */
    public function getByUuidEnabledOnDomain(string $uuid, int $domainId): Store
    {
        return $this->storeRepository->getByUuidEnabledOnDomain($uuid, $domainId);
    }

    /**
     * @param int $id
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Store\Store
     */
    public function getByIdEnabledOnDomain(int $id, int $domainId): Store
    {
        return $this->storeRepository->getByIdEnabledOnDomain($id, $domainId);
    }

    /**
     * @param int[] $storeIds
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    public function getStoresByIds(array $storeIds): array
    {
        return $this->storeRepository->getStoresByIds($storeIds);
    }
}
