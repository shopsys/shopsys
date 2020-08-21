<?php

declare(strict_types=1);

namespace App\Model\Stock\Future;

class FutureProductStockDataFactory
{
    /**
     * @return \App\Model\Stock\Future\FutureProductStockData
     */
    public function create(): FutureProductStockData
    {
        return new FutureProductStockData();
    }

    /**
     * @param \App\Model\Stock\Future\FutureProductStock $futureProductStock
     * @return \App\Model\Stock\Future\FutureProductStockData
     */
    public function createFromFutureProductStock(FutureProductStock $futureProductStock): FutureProductStockData
    {
        $futureProductStockData = $this->create();
        $futureProductStockData->erpId = $futureProductStock->getErpId();
        $futureProductStockData->sku = $futureProductStock->getSku();
        $futureProductStockData->storeCode = $futureProductStock->getStoreCode();
        $futureProductStockData->amount = $futureProductStock->getAmount();
        $futureProductStockData->dateExpectedArrival = $futureProductStock->getDateExpectedArrival();
        $futureProductStockData->dateConfirmedArrival = $futureProductStock->getDateConfirmedArrival();
        $futureProductStockData->isLate = $futureProductStock->isLate();

        return $futureProductStockData;
    }
}
