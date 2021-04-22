<?php

declare(strict_types=1);

namespace App\Model\Store;

use App\Component\Image\ImageFacade;
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
     * @param \App\Model\Store\StoreRepository $storeRepository
     * @param \App\Model\Store\StoreFactory $storeFactory
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        StoreRepository $storeRepository,
        StoreFactory $storeFactory,
        ImageFacade $imageFacade,
        EntityManagerInterface $em
    ) {
        $this->storeRepository = $storeRepository;
        $this->storeFactory = $storeFactory;
        $this->imageFacade = $imageFacade;
        $this->em = $em;
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

        return $store;
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
}
