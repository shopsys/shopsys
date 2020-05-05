<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Stock\StockData;
use App\Model\Stock\StockDataFactory;
use App\Model\Stock\StockFacade;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class StocksDataFixture extends AbstractReferenceFixture
{
    protected const ATTR_NAME = 'name';
    protected const ATTR_CENTRAL = 'centralStock';
    public const ATTR_EXTERNAL = 'externalId';

    public const STOCK_PREFIX = 'stock_';

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Stock\StockFacade
     */
    private $stockFacade;

    /**
     * @var \App\Model\Stock\StockDataFactory
     */
    private $stockDataFactory;

    /**
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Stock\StockFacade $stockFacade
     * @param \App\Model\Stock\StockDataFactory $stockDataFactory
     */
    public function __construct(Domain $domain, StockFacade $stockFacade, StockDataFactory $stockDataFactory)
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
            foreach ($this->getDemoData($domainId) as $demoRow) {
                $stock = $this->stockFacade->create($this->initStockData($domainId, $demoRow));
                $this->addReferenceForDomain(self::STOCK_PREFIX . $stock->getExternalId(), $stock, $domainId);
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
     * @param int $domainId
     * @return array
     */
    public static function getDemoData(int $domainId): array
    {
        if ($domainId > 2) {
            $domainId = 1;
        }

        $data[1] = [
                [
                    self::ATTR_NAME => 'Sklad Praha',
                    self::ATTR_CENTRAL => true,
                    self::ATTR_EXTERNAL => 'ppp',
                ],
                [
                    self::ATTR_NAME => 'Sklad Brno',
                    self::ATTR_CENTRAL => false,
                    self::ATTR_EXTERNAL => 'ddd',
                ],
                [
                    self::ATTR_NAME => 'Sklad Ostrava',
                    self::ATTR_CENTRAL => false,
                    self::ATTR_EXTERNAL => 'fff',
                ],
                [
                    self::ATTR_NAME => 'Sklad Pardubice',
                    self::ATTR_CENTRAL => false,
                    self::ATTR_EXTERNAL => 'ggg',
                ],
            ];
        $data[2] = [
            [
                self::ATTR_NAME => 'Sklad Bratislava',
                self::ATTR_CENTRAL => true,
                self::ATTR_EXTERNAL => 'hhh',
            ],
            [
                self::ATTR_NAME => 'Sklad Košice',
                self::ATTR_CENTRAL => false,
                self::ATTR_EXTERNAL => 'jjj',
            ],
            [
                self::ATTR_NAME => 'Sklad Zilina',
                self::ATTR_CENTRAL => false,
                self::ATTR_EXTERNAL => 'zzz',
            ],
        ];

        return $data[$domainId];
    }
}
