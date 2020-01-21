<?php

declare(strict_types=1);


namespace App\Model\Stock;


use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;

class StockDataFactory implements StockDataFactoryInterface
{

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    private $adminDomainTabsFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(AdminDomainTabsFacade $adminDomainTabsFacade)
    {
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
    }

    /**
     * @return \App\Model\Stock\StockData
     */
    public function create(): StockData
    {
        $stockData = new StockData();
        $stockData->domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $stockData->centralStock = false;
        return $stockData;
    }

    /**
     * @param \App\Model\Stock\Stock $stock
     * @return \App\Model\Stock\StockData
     */
    public function createFromStock(Stock $stock): StockData
    {
        $stockData = new StockData();
        $stockData->name = $stock->getName();
        $stockData->domainId = $stock->getDomainId();
        $stockData->centralStock = $stock->isCentralStock();
        $stockData->externalId = $stock->getExternalId();
        return $stockData;
    }

}