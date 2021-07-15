<?php

declare(strict_types=1);

namespace App\Model\Store;

use App\Component\Image\ImageFacade;
use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class StoreFacade
{
    /**
     * @var \App\Model\Store\StoreRepository
     */
    private StoreRepository $storeRepository;

    /**
     * @var \App\Model\Store\StoreFactory
     */
    private StoreFactory $storeFactory;

    /**
     * @var \App\Component\Image\ImageFacade
     */
    private ImageFacade $imageFacade;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @param \App\Model\Store\StoreRepository $storeRepository
     * @param \App\Model\Store\StoreFactory $storeFactory
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        StoreRepository $storeRepository,
        StoreFactory $storeFactory,
        FriendlyUrlFacade $friendlyUrlFacade,
        ImageFacade $imageFacade,
        EntityManagerInterface $em
    ) {
        $this->storeRepository = $storeRepository;
        $this->storeFactory = $storeFactory;
        $this->imageFacade = $imageFacade;
        $this->em = $em;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
    }

    /**
     * @return \App\Model\Store\Store[]
     */
    public function getAllStores(): array
    {
        return $this->storeRepository->getAll();
    }

    /**
     * @param \App\Model\Store\StoreData $storeData
     * @return \App\Model\Store\Store
     */
    public function create(StoreData $storeData): Store
    {
        $store = $this->storeFactory->create($storeData);
        $this->em->persist($store);
        $this->em->flush();

        $this->createFriendlyUrl($store);

        $this->imageFacade->manageImages($store, $storeData->image);

        return $store;
    }

    /**
     * @param int $id
     * @param \App\Model\Store\StoreData $storeData
     * @return \App\Model\Store\Store
     */
    public function edit(int $id, StoreData $storeData): Store
    {
        $store = $this->getById($id);
        $store->edit($storeData);
        $this->em->flush();

        $this->imageFacade->manageImages($store, $storeData->image);

        $this->createFriendlyUrl($store);

        return $store;
    }

    /**
     * @param \App\Model\Store\Store $store
     */
    private function createFriendlyUrl(Store $store): void
    {
        foreach ($store->getEnabledDomains() as $storeDomain) {
            $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                'front_stores_detail',
                $store->getId(),
                $store->getName(),
                $storeDomain->getDomainId()
            );
        }

        $this->em->flush();
    }

    /**
     * @param int $id
     * @return \App\Model\Store\Store
     */
    public function getById(int $id): Store
    {
        return $this->storeRepository->getById($id);
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
     * @param \App\Model\Store\Store $store
     */
    public function changeDefaultStore(Store $store): void
    {
        $this->storeRepository->changeDefaultStore($store);
    }

    /**
     * @param int $domainId
     * @return \App\Model\Store\Store[]
     */
    public function getStoresEnabledOnDomainIndexedByStoreId(int $domainId): array
    {
        $stores = $this->storeRepository->getStoresEnabledOnDomain($domainId);
        $storesById = [];
        foreach ($stores as $store) {
            $storesById[$store->getId()] = $store;
        }

        return $storesById;
    }

    /**
     * @param string $externalId
     * @return \App\Model\Store\Store|null
     */
    public function findStoreByExternalId(string $externalId): ?Store
    {
        return $this->storeRepository->findStoreByExternalId($externalId);
    }

    /**
     * @param int $domainId
     * @param int $limit
     * @param int $offset
     * @return \App\Model\Store\Store[]
     */
    public function getStoresListEnabledOnDomain(int $domainId, int $limit, int $offset): array
    {
        return $this->storeRepository->getStoresListEnabledOnDomain($domainId, $limit, $offset);
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
     * @return \App\Model\Store\Store
     */
    public function getByUuid(string $uuid): Store
    {
        return $this->storeRepository->getOneByUuid($uuid);
    }
}
