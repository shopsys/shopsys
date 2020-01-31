<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Stock\StockData;
use App\Model\Stock\StockDataFactoryInterface;
use App\Model\Stock\StockFacadeInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class StocksDataFixture extends AbstractReferenceFixture
{
    protected const ATTR_NAME = 'name';
    protected const ATTR_CENTRAL = 'centralStock';
    protected const ATTR_EXTERNAL = 'externalId';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Stock\StockFacadeInterface
     */
    private $stockFacade;

    /**
     * @var \App\Model\Stock\StockDataFactoryInterface
     */
    private $stockDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Stock\StockFacadeInterface $stockFacade
     * @param \App\Model\Stock\StockDataFactoryInterface $stockDataFactory
     */
    public function __construct(Domain $domain, StockFacadeInterface $stockFacade, StockDataFactoryInterface $stockDataFactory)
    {
        $this->domain = $domain;
        $this->stockFacade = $stockFacade;
        $this->stockDataFactory = $stockDataFactory;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            foreach ($this->getDemoData() as $demoRow) {
                $this->stockFacade->create($this->initStockData($domainId, $demoRow));
            }
        }
    }

    /**
     * @param int $domainId
     * @param mixed $demoRow
     * @return \App\Model\Stock\StockData
     */
    protected function initStockData(int $domainId, $demoRow): StockData
    {
        $stockData = $this->stockDataFactory->create();
        $stockData->domainId = $domainId;
        $stockData->name = $demoRow[self::ATTR_NAME];
        $stockData->centralStock = $demoRow[self::ATTR_CENTRAL];
        $stockData->externalId = $demoRow[self::ATTR_EXTERNAL];
        return $stockData;
    }

    /**
     * @return array
     */
    protected function getDemoData(): array
    {
        return [
            [
                self::ATTR_NAME => 'Sklad asd',
                self::ATTR_CENTRAL => true,
                self::ATTR_EXTERNAL => 'asd',
            ],
            [
                self::ATTR_NAME => 'Sklad ddd',
                self::ATTR_CENTRAL => false,
                self::ATTR_EXTERNAL => 'ddd',
            ],
            [
                self::ATTR_NAME => 'Sklad fff',
                self::ATTR_CENTRAL => false,
                self::ATTR_EXTERNAL => 'fff',
            ],
            [
                self::ATTR_NAME => 'Sklad ggg',
                self::ATTR_CENTRAL => false,
                self::ATTR_EXTERNAL => 'ggg',
            ],
            [
                self::ATTR_NAME => 'Sklad hhh',
                self::ATTR_CENTRAL => false,
                self::ATTR_EXTERNAL => 'hhh',
            ],
            [
                self::ATTR_NAME => 'Sklad jjj',
                self::ATTR_CENTRAL => false,
                self::ATTR_EXTERNAL => 'jjj',
            ],
        ];
    }
}
