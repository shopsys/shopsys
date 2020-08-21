<?php

declare(strict_types=1);

namespace App\Model\Stock\Future;

use Doctrine\ORM\EntityManagerInterface;

class FutureProductStockFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var \App\Model\Stock\Future\FutureProductStockRepository
     */
    private FutureProductStockRepository $futureProductStockRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Stock\Future\FutureProductStockRepository $futureProductStockRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        FutureProductStockRepository $futureProductStockRepository
    ) {
        $this->em = $em;
        $this->futureProductStockRepository = $futureProductStockRepository;
    }

    /**
     * @param \App\Model\Stock\Future\FutureProductStockData $futureProductStockData
     * @return \App\Model\Stock\Future\FutureProductStock
     */
    public function create(FutureProductStockData $futureProductStockData): FutureProductStock
    {
        $futureProductStock = new FutureProductStock($futureProductStockData);
        $this->em->persist($futureProductStock);
        $this->em->flush();

        return $futureProductStock;
    }

    /**
     * @param string $erpId
     * @param \App\Model\Stock\Future\FutureProductStockData $futureProductStockData
     * @return \App\Model\Stock\Future\FutureProductStock
     */
    public function edit(string $erpId, FutureProductStockData $futureProductStockData): FutureProductStock
    {
        $futureProductStock = $this->getByErpId($erpId);
        $futureProductStock->edit($futureProductStockData);
        $this->em->flush();

        return $futureProductStock;
    }

    /**
     * @param string $erpId
     */
    public function delete(string $erpId): void
    {
        $futureProductStock = $this->getByErpId($erpId);
        $this->em->remove($futureProductStock);
        $this->em->flush();
    }

    /**
     * @param string $erpId
     * @return \App\Model\Stock\Future\FutureProductStock
     */
    public function getByErpId(string $erpId): FutureProductStock
    {
        return $this->futureProductStockRepository->getByErpId($erpId);
    }

    /**
     * @param string $erpId
     * @return \App\Model\Stock\Future\FutureProductStock|null
     */
    public function findByErpId(string $erpId): ?FutureProductStock
    {
        return $this->futureProductStockRepository->findByErpId($erpId);
    }

    /**
     * @param string $sku
     * @param string $storeCode
     * @return \App\Model\Stock\Future\FutureProductStock|null
     */
    public function findClosestFutureProductStockBySkuAndStoreCode(string $sku, string $storeCode): ?FutureProductStock
    {
        return $this->futureProductStockRepository->findClosestFutureProductStockBySkuAndStoreCode($sku, $storeCode);
    }

    public function cleanOrdersAfterDeadline(): void
    {
        $this->futureProductStockRepository->cleanOrdersAfterDeadline();
    }
}
